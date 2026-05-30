#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::net::TcpStream;
use std::process::{Child, Command};
use std::sync::{Arc, Mutex};
use std::time::{Duration, Instant};
use tauri::{
    CustomMenuItem, Manager, SystemTray, SystemTrayEvent, SystemTrayMenu,
};

struct PhpServer(Arc<Mutex<Option<Child>>>);

fn wait_for_server(port: u16, timeout_secs: u64) -> bool {
    let addr = format!("127.0.0.1:{}", port);
    let deadline = Instant::now() + Duration::from_secs(timeout_secs);
    while Instant::now() < deadline {
        if TcpStream::connect(&addr).is_ok() {
            return true;
        }
        std::thread::sleep(Duration::from_millis(300));
    }
    false
}

#[tauri::command]
fn check_online() -> bool {
    let server_url = std::env::var("SYNC_SERVER_URL").unwrap_or_default();
    if server_url.is_empty() {
        return false;
    }
    // Extract host:port from URL for TCP check
    if let Ok(url) = url::Url::parse(&server_url) {
        let host = url.host_str().unwrap_or("").to_string();
        let port = url.port().unwrap_or(if url.scheme() == "https" { 443 } else { 80 });
        return TcpStream::connect(format!("{}:{}", host, port)).is_ok();
    }
    false
}

#[tauri::command]
async fn trigger_sync(state: tauri::State<'_, PhpServer>) -> Result<String, String> {
    // Run php artisan sync:perform via a new process
    let output = Command::new("php")
        .args(["artisan", "sync:perform"])
        .output()
        .map_err(|e| e.to_string())?;
    if output.status.success() {
        Ok(String::from_utf8_lossy(&output.stdout).to_string())
    } else {
        Err(String::from_utf8_lossy(&output.stderr).to_string())
    }
}

fn main() {
    let tray_menu = SystemTrayMenu::new()
        .add_item(CustomMenuItem::new("open", "Open HMS"))
        .add_item(CustomMenuItem::new("sync", "Sync Now"))
        .add_native_item(tauri::SystemTrayMenuItem::Separator)
        .add_item(CustomMenuItem::new("quit", "Quit"));

    let tray = SystemTray::new().with_menu(tray_menu).with_tooltip("HMS Desktop");

    let php_process: Arc<Mutex<Option<Child>>> = Arc::new(Mutex::new(None));
    let php_process_clone = php_process.clone();

    tauri::Builder::default()
        .manage(PhpServer(php_process.clone()))
        .setup(move |app| {
            // Start PHP sidecar
            let resource_path = app.path_resolver().resource_dir()
                .expect("Failed to get resource dir");
            let app_dir = resource_path.parent()
                .unwrap_or(&resource_path)
                .to_path_buf();

            let php_bin = app_dir.join("bin").join("php.exe");
            let php_bin_str = if php_bin.exists() {
                php_bin.to_string_lossy().to_string()
            } else {
                "php".to_string() // fall back to system PHP
            };

            let child = Command::new(&php_bin_str)
                .args(["artisan", "serve", "--port=8000", "--host=127.0.0.1"])
                .current_dir(&app_dir)
                .spawn()
                .expect("Failed to start PHP server");

            {
                let mut lock = php_process_clone.lock().unwrap();
                *lock = Some(child);
            }

            // Wait for server to be ready
            if !wait_for_server(8000, 30) {
                eprintln!("PHP server did not start in time");
            }

            // Open main window
            let window = app.get_window("main").unwrap();
            window.show().unwrap();

            Ok(())
        })
        .system_tray(tray)
        .on_system_tray_event(|app, event| {
            if let SystemTrayEvent::MenuItemClick { id, .. } = event {
                match id.as_str() {
                    "open" => {
                        if let Some(w) = app.get_window("main") {
                            w.show().unwrap();
                            w.set_focus().unwrap();
                        }
                    }
                    "sync" => {
                        let _ = Command::new("php")
                            .args(["artisan", "sync:perform"])
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
                event.window().hide().unwrap();
                api.prevent_close();
            }
        })
        .invoke_handler(tauri::generate_handler![check_online, trigger_sync])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
