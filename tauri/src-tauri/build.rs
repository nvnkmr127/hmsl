use std::fs;
use std::path::Path;

fn main() {
    // Copy .env.offline to .env in the Laravel root (../../.env) if it doesn't exist
    let env_offline = Path::new("../../.env.offline");
    let env_dest = Path::new("../../.env");
    if env_offline.exists() && !env_dest.exists() {
        let _ = fs::copy(env_offline, env_dest);
    }

    // Ensure database/database.sqlite exists in the Laravel root (../../database/database.sqlite)
    let db_dir = Path::new("../../database");
    let db_file = db_dir.join("database.sqlite");
    if !db_dir.exists() {
        let _ = fs::create_dir_all(db_dir);
    }
    if !db_file.exists() {
        let _ = fs::write(&db_file, []);
    }

    tauri_build::build();
}
