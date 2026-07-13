#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::net::{TcpListener, TcpStream};
use std::sync::{Arc, Mutex};
use std::time::{Duration, Instant};
use tauri::{
    CustomMenuItem, Manager, SystemTray, SystemTrayEvent, SystemTrayMenu,
    GlobalShortcutManager,
};


struct AppConfig {
    hmsl_dir: std::path::PathBuf,
}

struct PhpServer(Arc<Mutex<Option<std::process::Child>>>);
struct SyncDaemonProcess(Arc<Mutex<Option<std::process::Child>>>);



fn is_port_in_use(port: u16) -> bool {
    TcpListener::bind(("127.0.0.1", port)).is_err()
}

async fn wait_for_php_ready(max_seconds: u64) -> bool {
    let client = reqwest::Client::new();
    // Ping the root URL — Laravel returns 302 redirect for unauthenticated users,
    // which still proves the server is up and routing.
    let url = "http://127.0.0.1:8000/";
    let start = Instant::now();
    let timeout = Duration::from_secs(max_seconds);

    while start.elapsed() < timeout {
        if let Ok(_resp) = client.get(url).timeout(Duration::from_millis(800)).send().await {
            // Any HTTP response (200, 302, 404, 500) means PHP+Laravel is alive
            return true;
        }
        tokio::time::sleep(Duration::from_millis(500)).await;
    }
    false
}

fn strip_unc_prefix(path: std::path::PathBuf) -> std::path::PathBuf {
    let path_str = path.to_string_lossy();
    if path_str.starts_with(r"\\?\") {
        std::path::PathBuf::from(&path_str[4..])
    } else {
        path
    }
}

fn find_hmsl_dir(app: &tauri::App) -> std::path::PathBuf {
    // On an installed machine, Tauri bundles resources into:
    //   <InstallDir>\resources\  (alongside the .exe)
    // The Laravel files (artisan, app/, etc.) land directly in resources/
    // because tauri.conf.json maps them as  "../../artisan" etc.
    // We must check all possible locations.

    let candidates: Vec<std::path::PathBuf> = {
        let mut v = vec![];

        if let Some(resource_dir) = app.path_resolver().resource_dir() {
            // Installed: resources/_up_/_up_/artisan  (../../ paths in tauri.conf.json)
            v.push(resource_dir.join("_up_").join("_up_"));
            // Flat: resources/artisan
            v.push(resource_dir.clone());
            // One level up: exe_dir/artisan
            if let Some(p) = resource_dir.parent() {
                v.push(p.to_path_buf());
                if let Some(g) = p.parent() {
                    v.push(g.to_path_buf());
                }
            }
        }

        // Exe-relative paths
        if let Ok(exe) = std::env::current_exe() {
            if let Some(exe_dir) = exe.parent() {
                v.push(exe_dir.join("resources").join("_up_").join("_up_"));
                v.push(exe_dir.join("resources"));
                v.push(exe_dir.to_path_buf());
            }
        }

        // Dev: walk up from cwd looking for artisan
        if let Ok(cwd) = std::env::current_dir() {
            let mut dir = cwd;
            let mut loop_count = 0;
            loop {
                v.push(dir.clone());
                if !dir.pop() || loop_count > 10 { break; }
                loop_count += 1;
            }
        }

        v
    };

    for candidate in &candidates {
        if candidate.join("artisan").exists() {
            return strip_unc_prefix(candidate.clone());
        }
    }

    // Absolute fallback
    let fallback = app.path_resolver().resource_dir()
        .map(|r| r.join("_up_").join("_up_"))
        .unwrap_or_else(|| std::env::current_dir().unwrap_or_default());
    strip_unc_prefix(fallback)
}


fn write_startup_log(msg: &str) {
    let log_path = std::env::var("APPDATA")
        .map(|p| std::path::PathBuf::from(p).join("HMS").join("startup.log"))
        .unwrap_or_else(|_| std::path::PathBuf::from("C:\\HMS_startup.log"));

    let _ = std::fs::create_dir_all(log_path.parent().unwrap());

    let timestamp = std::time::SystemTime::now()
        .duration_since(std::time::UNIX_EPOCH)
        .map(|d| d.as_secs())
        .unwrap_or(0);

    let line = format!("[{}] {}\n", timestamp, msg);

    use std::io::Write;
    if let Ok(mut file) = std::fs::OpenOptions::new()
        .create(true).append(true).open(&log_path) {
        let _ = file.write_all(line.as_bytes());
    }
}
fn ensure_php_files_in_root(hmsl_dir: &std::path::Path) {
    let (php_ini, _) = get_php_paths_from_hmsl(hmsl_dir);
    let php_src_dir = if php_ini.exists() {
        php_ini.parent().unwrap().to_path_buf()
    } else {
        write_startup_log("WARNING: php.ini source not found, cannot copy DLLs.");
        return;
    };

    if let Ok(exe_path) = std::env::current_exe() {
        if let Some(exe_dir) = exe_path.parent() {
            write_startup_log(&format!("Copying PHP files from {:?} to {:?}", php_src_dir, exe_dir));
            if php_src_dir == exe_dir {
                write_startup_log("PHP source and exe dir are the same, skipping copy.");
                return;
            }
            if let Ok(entries) = std::fs::read_dir(&php_src_dir) {
                for entry in entries.flatten() {
                    let path = entry.path();
                    if path.is_file() {
                        if let Some(filename) = path.file_name() {
                            let dest = exe_dir.join(filename);
                            let should_copy = if !dest.exists() {
                                true
                            } else {
                                let src_meta = entry.metadata().ok();
                                let dest_meta = std::fs::metadata(&dest).ok();
                                src_meta.map(|m| m.len()) != dest_meta.map(|m| m.len())
                            };
                            if should_copy {
                                match std::fs::copy(&path, &dest) {
                                    Ok(_) => write_startup_log(&format!("Copied PHP root file: {:?}", filename)),
                                    Err(e) => write_startup_log(&format!("ERROR copying PHP root file {:?}: {}", filename, e)),
                                }
                            }
                        }
                    } else if path.is_dir() {
                        if let Some(dirname) = path.file_name() {
                            if dirname == "ext" || dirname == "lib" {
                                let dest_dir = exe_dir.join(dirname);
                                let _ = std::fs::create_dir_all(&dest_dir);
                                if let Ok(sub_entries) = std::fs::read_dir(&path) {
                                    for sub_entry in sub_entries.flatten() {
                                        let sub_path = sub_entry.path();
                                        if sub_path.is_file() {
                                            if let Some(sub_filename) = sub_path.file_name() {
                                                let dest_file = dest_dir.join(sub_filename);
                                                let should_copy = if !dest_file.exists() {
                                                    true
                                                } else {
                                                    let src_meta = sub_entry.metadata().ok();
                                                    let dest_meta = std::fs::metadata(&dest_file).ok();
                                                    src_meta.map(|m| m.len()) != dest_meta.map(|m| m.len())
                                                };
                                                if should_copy {
                                                    if let Err(e) = std::fs::copy(&sub_path, &dest_file) {
                                                        write_startup_log(&format!("ERROR copying PHP subfile {:?}: {}", sub_filename, e));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

fn get_php_executable_path(hmsl_dir: &std::path::Path) -> std::path::PathBuf {
    if let Ok(exe_path) = std::env::current_exe() {
        if let Some(exe_dir) = exe_path.parent() {
            // Installed app: use bundled php.exe inside php_bin/ so that php8.dll
            // and all other core DLLs are found right next to the executable.
            let php_in_binaries = exe_dir.join("php_bin").join("php.exe");
            if php_in_binaries.exists() {
                write_startup_log(&format!("PHP exe (installed binaries): {:?}", php_in_binaries));
                return php_in_binaries;
            }
            // Dev mode: the Tauri sidecar lives alongside the debug exe
            let php_sidecar = exe_dir.join("php-x86_64-pc-windows-msvc.exe");
            if php_sidecar.exists() {
                write_startup_log(&format!("PHP exe (dev sidecar): {:?}", php_sidecar));
                return php_sidecar;
            }
        }
    }
    // Fallbacks for non-standard layouts
    let candidates = vec![
        hmsl_dir.parent().and_then(|p| p.parent()).map(|r| r.join("php_bin").join("php.exe")),
        hmsl_dir.parent()
            .and_then(|p| p.parent())
            .and_then(|p| p.parent())
            .and_then(|p| p.parent())
            .map(|r| r.join("binaries").join("php-x86_64-pc-windows-msvc.exe")),
    ];
    for candidate in candidates.into_iter().flatten() {
        if candidate.exists() {
            return candidate;
        }
    }
    std::path::PathBuf::from("php.exe")
}


fn get_env_var(hmsl_dir: &std::path::Path, key: &str) -> Option<String> {
    // 1. First check system env var
    if let Ok(val) = std::env::var(key) {
        if !val.is_empty() {
            return Some(val);
        }
    }
    // 2. Read from .env file in hmsl_dir
    let env_path = hmsl_dir.join(".env");
    if let Ok(content) = std::fs::read_to_string(env_path) {
        for line in content.lines() {
            let line = line.trim();
            if line.starts_with('#') || line.is_empty() {
                continue;
            }
            if let Some((k, v)) = line.split_once('=') {
                if k.trim() == key {
                    let val = v.trim().trim_matches('"').trim_matches('\'').to_string();
                    return Some(val);
                }
            }
        }
    }
    None
}

fn get_php_paths_from_hmsl(hmsl_dir: &std::path::Path) -> (std::path::PathBuf, std::path::PathBuf) {
    let mut candidates: Vec<std::path::PathBuf> = vec![];

    // Highest priority: exe-relative php_bin/  (Windows installed: resource_dir == exe_dir)
    if let Ok(exe) = std::env::current_exe() {
        if let Some(exe_dir) = exe.parent() {
            candidates.push(exe_dir.join("php_bin"));
        }
    }

    // Installed: hmsl_dir = <install>/_up_/_up_  →  parent×2 = <install>  →  php_bin
    if let Some(p) = hmsl_dir.parent().and_then(|p| p.parent()) {
        candidates.push(p.join("php_bin"));
    }

    // Dev builds: target/debug/_up_/_up_  →  4×parent = src-tauri  →  php_bin
    if let Some(p) = hmsl_dir.parent()
        .and_then(|p| p.parent())
        .and_then(|p| p.parent())
        .and_then(|p| p.parent())
    {
        candidates.push(p.join("php_bin"));
    }
    if let Some(p) = hmsl_dir.parent()
        .and_then(|p| p.parent())
        .and_then(|p| p.parent())
    {
        candidates.push(p.join("php_bin"));
    }

    // Flat layout
    candidates.push(hmsl_dir.join("php_bin"));
    // Dev tree from Laravel root
    candidates.push(hmsl_dir.join("tauri").join("src-tauri").join("php_bin"));

    for candidate in &candidates {
        let ini = candidate.join("php.ini");
        write_startup_log(&format!("  PHP ini candidate: {:?} exists={}", ini, ini.exists()));
        if ini.exists() {
            return (ini, candidate.join("ext"));
        }
    }

    (std::path::PathBuf::from("php.ini"), std::path::PathBuf::from("ext"))
}

fn check_connectivity_internal(hmsl_dir: &std::path::Path) -> bool {
    let server_url = get_env_var(hmsl_dir, "SYNC_SERVER_URL").unwrap_or_default();
    if server_url.is_empty() {
        return false;
    }
    let clean_url = server_url.trim_end_matches('/');
    let ping_url = format!("{}/api/v1/ping", clean_url);
    match ureq::get(&ping_url)
        .timeout(Duration::from_secs(5))
        .call() {
            Ok(resp) => resp.status() == 200,
            Err(_) => false,
        }
}

fn run_sync_internal(hmsl_dir: &std::path::Path) -> Result<String, String> {
    let cmd = tauri::api::process::Command::new_sidecar("php")
        .map_err(|e| format!("Failed to create sidecar command: {}", e))?;

    let (php_ini, php_ext) = get_php_paths_from_hmsl(hmsl_dir);

    let output = cmd
        .args([
            "-c", &php_ini.to_string_lossy(),
            "-d", &format!("extension_dir={}", php_ext.to_string_lossy()),
            "artisan", "sync:perform"
        ])
        .current_dir(hmsl_dir.to_path_buf())
        .output()
        .map_err(|e| format!("Failed to execute sync command: {}", e))?;

    if output.status.success() {
        Ok(output.stdout)
    } else {
        Err(output.stderr)
    }
}

#[tauri::command]
fn check_online(config: tauri::State<'_, AppConfig>) -> bool {
    let server_url = get_env_var(&config.hmsl_dir, "SYNC_SERVER_URL").unwrap_or_default();
    if server_url.is_empty() {
        return false;
    }
    if let Ok(url) = url::Url::parse(&server_url) {
        let host = url.host_str().unwrap_or("").to_string();
        let port = url.port().unwrap_or(if url.scheme() == "https" { 443 } else { 80 });
        return TcpStream::connect(format!("{}:{}", host, port)).is_ok();
    }
    false
}

#[tauri::command]
async fn trigger_sync(config: tauri::State<'_, AppConfig>) -> Result<String, String> {
    run_sync_internal(&config.hmsl_dir)
}

#[tauri::command]
fn get_sync_status(config: tauri::State<'_, AppConfig>) -> Result<serde_json::Value, String> {
    let path = config.hmsl_dir.join("storage").join("app").join("sync_status.json");
    if !path.exists() {
        return Err("Sync status file not found".to_string());
    }
    let content = std::fs::read_to_string(&path)
        .map_err(|e| format!("Failed to read sync status file: {}", e))?;
    let json: serde_json::Value = serde_json::from_str(&content)
        .map_err(|e| format!("Failed to parse sync status JSON: {}", e))?;
    Ok(json)
}

#[tauri::command]
fn check_server_connectivity(config: tauri::State<'_, AppConfig>) -> bool {
    check_connectivity_internal(&config.hmsl_dir)
}

#[tauri::command]
async fn trigger_update_check(_app_handle: tauri::AppHandle) -> Result<String, String> {
    Ok("Auto-update is disabled in this build.".to_string())
}

#[tauri::command]
fn restart_sync_daemon(
    config: tauri::State<'_, AppConfig>,
    daemon: tauri::State<'_, SyncDaemonProcess>,
) -> Result<String, String> {
    // 1. Kill existing if running
    {
        let mut lock = daemon.0.lock().unwrap();
        if let Some(mut child) = lock.take() {
            let _ = child.kill();
        }
    }
    
    // 2. Spawn again
    let php_bin = get_php_executable_path(&config.hmsl_dir);
    let (php_ini, php_ext) = get_php_paths_from_hmsl(&config.hmsl_dir);
    use std::os::windows::process::CommandExt;
    const CREATE_NO_WINDOW: u32 = 0x08000000;
    
    let c = std::process::Command::new(&php_bin)
        .args([
            "-c", &php_ini.to_string_lossy(),
            "-d", &format!("extension_dir={}", php_ext.to_string_lossy()),
            "artisan", "sync:daemon"
        ])
        .current_dir(config.hmsl_dir.clone())
        .creation_flags(CREATE_NO_WINDOW)
        .spawn()
        .map_err(|e| format!("Failed to spawn daemon: {}", e))?;
        
    {
        let mut lock = daemon.0.lock().unwrap();
        *lock = Some(c);
    }
    
    Ok("Sync Daemon restarted successfully.".to_string())
}

#[tauri::command]
fn open_pdf(path: String) -> Result<(), String> {
    std::process::Command::new("cmd")
        .args(["/c", "start", "", &path])
        .spawn()
        .map_err(|e| format!("Failed to open PDF: {}", e))?;
    Ok(())
}

#[tauri::command]
async fn open_pdf_url(url: String) -> Result<(), String> {
    let client = reqwest::Client::new();
    let filename = url.split('/').last().unwrap_or("document").to_string() + ".pdf";
    let temp_dir = std::env::temp_dir();
    let file_path = temp_dir.join(filename);
    
    let resp = client.get(&url)
        .send()
        .await
        .map_err(|e| format!("Failed to download PDF: {}", e))?;
        
    let bytes = resp.bytes()
        .await
        .map_err(|e| format!("Failed to read PDF bytes: {}", e))?;
        
    std::fs::write(&file_path, bytes)
        .map_err(|e| format!("Failed to save PDF: {}", e))?;
        
    std::process::Command::new("cmd")
        .args(["/c", "start", "", &file_path.to_string_lossy()])
        .spawn()
        .map_err(|e| format!("Failed to open PDF: {}", e))?;
        
    Ok(())
}


fn main() {
    let tray_menu = SystemTrayMenu::new()
        .add_item(CustomMenuItem::new("open", "Open HMS"))
        .add_item(CustomMenuItem::new("sync", "Sync Now"))
        .add_item(CustomMenuItem::new("update", "Check for Updates"))
        .add_item(CustomMenuItem::new("open_log", "View Startup Log"))
        .add_native_item(tauri::SystemTrayMenuItem::Separator)
        .add_item(CustomMenuItem::new("quit", "Quit"));

    let tray = SystemTray::new().with_menu(tray_menu).with_tooltip("HMS Desktop");

    let php_process: Arc<Mutex<Option<std::process::Child>>> = Arc::new(Mutex::new(None));
    let php_process_clone = php_process.clone();
    let php_process_for_run = php_process.clone();

    let daemon_process: Arc<Mutex<Option<std::process::Child>>> = Arc::new(Mutex::new(None));
    let daemon_process_clone = daemon_process.clone();
    let daemon_process_for_run = daemon_process.clone();

    let app = tauri::Builder::default()
        .manage(PhpServer(php_process.clone()))
        .manage(SyncDaemonProcess(daemon_process.clone()))
        .setup(move |app| {
            // Find HMSL project directory
            let hmsl_dir = find_hmsl_dir(app);
            write_startup_log(&format!("=== HMS Desktop starting ==="));
            write_startup_log(&format!("Laravel root: {:?}", hmsl_dir));
            write_startup_log(&format!("artisan exists: {}", hmsl_dir.join("artisan").exists()));
            write_startup_log(&format!(".env exists: {}", hmsl_dir.join(".env").exists()));
            write_startup_log(&format!(".env.offline exists: {}", hmsl_dir.join(".env.offline").exists()));

            // Copy PHP DLLs and supporting folders to the same folder as sidecar executables
            ensure_php_files_in_root(&hmsl_dir);

            // Ensure .env exists (copy from .env.offline on first install)
            let env_file = hmsl_dir.join(".env");
            if !env_file.exists() {
                let offline = hmsl_dir.join(".env.offline");
                if offline.exists() {
                    let _ = std::fs::copy(&offline, &env_file);
                    write_startup_log("Copied .env.offline -> .env");
                } else {
                    write_startup_log("WARNING: neither .env nor .env.offline found!");
                }
            }

            // Ensure writable directories exist
            for dir in &[
                "storage/app/public",
                "storage/framework/cache/data",
                "storage/framework/sessions",
                "storage/framework/views",
                "storage/logs",
                "bootstrap/cache",
            ] {
                let _ = std::fs::create_dir_all(hmsl_dir.join(dir));
            }

            app.manage(AppConfig { hmsl_dir: hmsl_dir.clone() });

            // Retrieve main window (already shows splash via tauri.conf.json url)
            let main_window = app.get_window("main").unwrap();

            // Prevent right-click context menu
            let _ = main_window.eval("document.addEventListener('contextmenu', e => e.preventDefault())");

            // Inject splash screen HTML (about:blank is the initial url so we inject directly)
            let splash_html = r#"
                document.open();
                document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>HMS - Loading</title><style>*{box-sizing:border-box;margin:0;padding:0}body{background:#111827;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh}.wrap{text-align:center}.logo{font-size:2.2em;font-weight:800;color:#818cf8;letter-spacing:4px;margin-bottom:28px;text-transform:uppercase}.spinner{border:4px solid rgba(255,255,255,.1);width:52px;height:52px;border-radius:50%;border-left-color:#818cf8;animation:spin 1s linear infinite;margin:0 auto 20px}.status{color:#9ca3af;font-size:14px;margin-top:8px}@keyframes spin{to{transform:rotate(360deg)}}</style></head><body><div class="wrap"><div class="logo">HMS</div><div class="spinner"></div><p class="status" id="s">Starting server...</p></div></body></html>');
                document.close();
            "#;
            let _ = main_window.eval(splash_html);


            let port_in_use = is_port_in_use(8000);
            if !port_in_use {
                let php_bin = get_php_executable_path(&hmsl_dir);
                let (php_ini, php_ext) = get_php_paths_from_hmsl(&hmsl_dir);
                write_startup_log(&format!("PHP bin path: {:?}", php_bin));
                write_startup_log(&format!("php.ini path: {:?}", php_ini));
                write_startup_log(&format!("php.ini exists: {}", php_ini.exists()));
                write_startup_log(&format!("php ext dir: {:?}", php_ext));
                write_startup_log(&format!("php ext exists: {}", php_ext.exists()));

                use std::os::windows::process::CommandExt;
                const CREATE_NO_WINDOW: u32 = 0x08000000;

                let appdata = std::env::var("APPDATA")
                    .map(|p| std::path::PathBuf::from(p).join("HMS"))
                    .unwrap_or_else(|_| std::path::PathBuf::from("C:\\HMS_startup.log").parent().unwrap().to_path_buf());
                let _ = std::fs::create_dir_all(&appdata);
                let log_file = std::fs::OpenOptions::new()
                    .create(true).append(true).open(appdata.join("php_spawn.log")).ok();
                let log_file_err = log_file.as_ref().and_then(|f| f.try_clone().ok());

                let router_path = hmsl_dir.join("vendor").join("laravel").join("framework").join("src").join("Illuminate").join("Foundation").join("resources").join("server.php");
                let spawned = std::process::Command::new(&php_bin)
                    .args([
                        "-c", &php_ini.to_string_lossy(),
                        "-d", &format!("extension_dir={}", php_ext.to_string_lossy()),
                        "-S", "127.0.0.1:8000",
                        &router_path.to_string_lossy()
                    ])
                    .current_dir(hmsl_dir.join("public"))
                    .stdout(log_file.map(std::process::Stdio::from).unwrap_or(std::process::Stdio::null()))
                    .stderr(log_file_err.map(std::process::Stdio::from).unwrap_or(std::process::Stdio::null()))
                    .creation_flags(CREATE_NO_WINDOW)
                    .spawn();

                match spawned {
                    Ok(c) => {
                        write_startup_log("PHP process spawned successfully via std::process::Command");
                        {
                            let mut lock = php_process_clone.lock().unwrap();
                            *lock = Some(c);
                        }
                    }
                    Err(e) => {
                        write_startup_log(&format!("ERROR spawning PHP via std::process::Command: {}", e));
                    }
                }
            } else {
                write_startup_log("Port 8000 is already in use. Skipping PHP server launch.");
            }

            // Show startup splash notification
            let identifier = app.config().tauri.bundle.identifier.clone();
            let _ = tauri::api::notification::Notification::new(identifier)
                .title("HMS Desktop")
                .body("HMS is starting...")
                .show();

            // Spawn background thread to wait for PHP readiness, run setup, and navigate
            let hmsl_dir_clone = hmsl_dir.clone();
            let main_window_clone = main_window.clone();
            tauri::async_runtime::spawn(async move {
                // Wait up to 30 seconds for the server to become ready
                let ready = wait_for_php_ready(60).await;
                write_startup_log(&format!("PHP ready: {}", ready));
                
                if ready {
                    // Update status text (id="s" in splash HTML)
                    let _ = main_window_clone.eval("let s=document.getElementById('s');if(s)s.innerText='Loading application...';");
                    
                    // Check if first-run setup is needed
                    let has_first_run = hmsl_dir_clone.join(".first_run_complete").exists();
                    if !has_first_run {
                        let _ = main_window_clone.eval("let s=document.getElementById('s');if(s)s.innerText='Setting up database for first use...';");
                        
                        let php_bin = get_php_executable_path(&hmsl_dir_clone);
                        let (php_ini, php_ext) = get_php_paths_from_hmsl(&hmsl_dir_clone);
                        let output = tokio::task::spawn_blocking(move || {
                            use std::os::windows::process::CommandExt;
                            const CREATE_NO_WINDOW: u32 = 0x08000000;
                            std::process::Command::new(&php_bin)
                                .args([
                                    "-c", &php_ini.to_string_lossy(),
                                    "-d", &format!("extension_dir={}", php_ext.to_string_lossy()),
                                    "artisan", "app:first-run"
                                ])
                                .current_dir(hmsl_dir_clone.clone())
                                .creation_flags(CREATE_NO_WINDOW)
                                .output()
                        }).await;
                        
                        match output {
                            Ok(Ok(out)) => {
                                let stdout_str = String::from_utf8_lossy(&out.stdout);
                                let stderr_str = String::from_utf8_lossy(&out.stderr);
                                write_startup_log(&format!("First-run stdout: {}", stdout_str));
                                write_startup_log(&format!("First-run stderr: {}", stderr_str));
                                if !out.status.success() {
                                    write_startup_log("ERROR: first-run setup failed (non-zero exit)");
                                }
                            }
                            _ => {
                                write_startup_log("ERROR: failed to wait for first-run setup");
                            }
                        }
                    }
                    
                    // Navigate to the main web app
                    let _ = main_window_clone.eval("window.location.href = 'http://127.0.0.1:8000';");
                } else {
                    let _ = main_window_clone.eval("let s=document.getElementById('s');if(s)s.innerText='PHP server failed to start. Please reinstall.';");
                    let _ = tauri::api::dialog::message(
                        None::<&tauri::Window>,
                        "PHP Server Error",
                        "PHP server failed to start. Check that PHP 8.2 is installed."
                    );
                }
            });

            // Spawn Sync Daemon process
            let php_bin = get_php_executable_path(&hmsl_dir);
            let (php_ini, php_ext) = get_php_paths_from_hmsl(&hmsl_dir);
            use std::os::windows::process::CommandExt;
            const CREATE_NO_WINDOW: u32 = 0x08000000;
            let daemon_spawned = std::process::Command::new(&php_bin)
                .args([
                    "-c", &php_ini.to_string_lossy(),
                    "-d", &format!("extension_dir={}", php_ext.to_string_lossy()),
                    "artisan", "sync:daemon"
                ])
                .current_dir(hmsl_dir.clone())
                .creation_flags(CREATE_NO_WINDOW)
                .spawn();

            match daemon_spawned {
                Ok(c) => {
                    let mut lock = daemon_process_clone.lock().unwrap();
                    *lock = Some(c);
                    write_startup_log("Sync Daemon spawned successfully via std::process::Command");
                }
                Err(e) => {
                    write_startup_log(&format!("ERROR spawning Sync Daemon: {}", e));
                }
            }

            // Register Local Keyboard Shortcuts
            let app_handle_for_shortcuts = app.handle();
            let mut shortcut_manager = app.global_shortcut_manager();
            
            // 1. Ctrl+R: Refresh (only if window is focused)
            let app_handle_clone1 = app_handle_for_shortcuts.clone();
            let _ = shortcut_manager.register("Ctrl+R", move || {
                if let Some(window) = app_handle_clone1.get_window("main") {
                    if window.is_focused().unwrap_or(false) {
                        let _ = window.eval("window.location.reload()");
                    }
                }
            });

            // 2. F11: Toggle Fullscreen
            let app_handle_clone2 = app_handle_for_shortcuts.clone();
            let _ = shortcut_manager.register("F11", move || {
                if let Some(window) = app_handle_clone2.get_window("main") {
                    if window.is_focused().unwrap_or(false) {
                        let is_fullscreen = window.is_fullscreen().unwrap_or(false);
                        let _ = window.set_fullscreen(!is_fullscreen);
                    }
                }
            });

            // 3. Ctrl+Shift+I: Open Developer Tools (Debug build only)
            #[cfg(debug_assertions)]
            {
                let app_handle_clone3 = app_handle_for_shortcuts.clone();
                let _ = shortcut_manager.register("Ctrl+Shift+I", move || {
                    if let Some(window) = app_handle_clone3.get_window("main") {
                        if window.is_focused().unwrap_or(false) {
                            window.open_devtools();
                        }
                    }
                });
            }

            // Spawn background thread for connectivity checks, tray tooltips, and auto-sync
            let hmsl_dir_for_sync = hmsl_dir.clone();
            let app_handle_for_sync = app.handle();
            std::thread::spawn(move || {
                let mut was_online: Option<bool> = None;
                loop {
                    // Check server connectivity
                    let is_online = check_connectivity_internal(&hmsl_dir_for_sync);
                    
                    // Trigger actions on state change
                    if was_online != Some(is_online) {
                        // Emit connectivity-changed event to webview
                        let _ = app_handle_for_sync.emit_all(
                            "connectivity-changed",
                            serde_json::json!({ "online": is_online })
                        );

                        if is_online {
                            // Offline -> Online transition: immediately trigger sync
                            println!("Connection restored! Triggering immediate sync...");
                            let _ = app_handle_for_sync.tray_handle().set_tooltip("HMS Desktop - â— Online");
                            
                            let hmsl_clone = hmsl_dir_for_sync.clone();
                            std::thread::spawn(move || {
                                match run_sync_internal(&hmsl_clone) {
                                    Ok(msg) => println!("Immediate restore sync success: {}", msg),
                                    Err(err) => eprintln!("Immediate restore sync failed: {}", err),
                                }
                            });
                        } else {
                            // Online -> Offline transition
                            let _ = app_handle_for_sync.tray_handle().set_tooltip("HMS Desktop - â—‹ Offline - HMS running locally");
                            println!("Connection lost. Working offline.");
                        }
                        
                        was_online = Some(is_online);
                    }
                    
                    std::thread::sleep(Duration::from_secs(15));
                }
            });

            Ok(())
        })
        .system_tray(tray)
        .on_system_tray_event(|app, event| {
            if let SystemTrayEvent::MenuItemClick { id, .. } = event {
                match id.as_str() {
                    "open" => {
                        if let Some(w) = app.get_window("main") {
                            let _ = w.show();
                            let _ = w.set_focus();
                        }
                    }
                    "sync" => {
                        let config = app.state::<AppConfig>();
                        let hmsl_dir = config.hmsl_dir.clone();
                        std::thread::spawn(move || {
                            match run_sync_internal(&hmsl_dir) {
                                Ok(msg) => println!("Tray sync success: {}", msg),
                                Err(err) => eprintln!("Tray sync failed: {}", err),
                            }
                        });
                    }
                    "update" => {
                        let app_handle = app.clone();
                        tauri::async_runtime::spawn(async move {
                            match trigger_update_check(app_handle).await {
                                Ok(msg) => println!("Tray update check success: {}", msg),
                                Err(err) => eprintln!("Tray update check failed: {}", err),
                            }
                        });
                    }
                    "open_log" => {
                        let log_path = std::env::var("APPDATA")
                            .map(|p| format!("{}\\HMS\\startup.log", p))
                            .unwrap_or_else(|_| "C:\\HMS_startup.log".to_string());
                        let _ = std::process::Command::new("notepad.exe")
                            .arg(&log_path)
                            .spawn();
                    }
                    "quit" => {
                        app.exit(0);
                    }
                    _ => {}
                }
            }
        })
        .on_window_event(|event| {
            if let tauri::WindowEvent::CloseRequested { api, .. } = event.event() {
                let window = event.window();
                let _ = window.hide();
                api.prevent_close();

                // Show native notification ONLY ONCE on tray minimize
                let app_handle = window.app_handle();
                if let Some(local_data_dir) = app_handle.path_resolver().app_local_data_dir() {
                    let marker_path = local_data_dir.join(".notification_shown");
                    if !marker_path.exists() {
                        let _ = std::fs::create_dir_all(&local_data_dir);
                        let _ = std::fs::write(&marker_path, "1");
                        
                        let identifier = app_handle.config().tauri.bundle.identifier.clone();
                        let _ = tauri::api::notification::Notification::new(identifier)
                            .title("HMS Desktop")
                            .body("HMS is still running in the system tray.")
                            .show();
                    }
                }
            }
        })
        .invoke_handler(tauri::generate_handler![
            check_online,
            trigger_sync,
            get_sync_status,
            check_server_connectivity,
            trigger_update_check,
            restart_sync_daemon,
            open_pdf,
            open_pdf_url
        ])
        .build(tauri::generate_context!())
        .expect("error while building tauri application");

    app.run(move |_app_handle, event| {
        if let tauri::RunEvent::ExitRequested { .. } = event {
            // Kill PHP server
            {
                let mut lock = php_process_for_run.lock().unwrap();
                if let Some(mut child) = lock.take() {
                    let _ = child.kill();
                    println!("PHP server process killed on app exit.");
                }
            }
            // Kill Sync Daemon
            {
                let mut lock = daemon_process_for_run.lock().unwrap();
                if let Some(mut child) = lock.take() {
                    let _ = child.kill();
                    println!("Sync Daemon process killed on app exit.");
                }
            }
        }
    });
}

