<?PHP

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 


    if ($oldversion < 112) {
//       delete_records("log_display", "module", "lesson");
    }

    
    return true;
}

?>
