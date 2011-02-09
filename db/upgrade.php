<?php  //$Id: upgrade.php,v 1.1.2.2 2009/02/23 19:22:42 dlnsk Exp $

// This file keeps track of upgrades to 
// the forum module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_attforblock_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes


    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

	if ($oldversion < 2008021904) { //New version in version.php
		global $USER;
		if ($sessions = $DB->get_records('attendance_sessions', array('takenby'=> 0))) {
			foreach ($sessions as $sess) {
				if ($DB->count_records('attendance_log', array('attsid'=> $sess->id)) > 0) {
					$sess->takenby = $USER->id;
					$sess->timetaken = $sess->timemodified ? $sess->timemodified : time();
					$sess->description = addslashes($sess->description);
					$result = $DB->update_record('attendance_sessions', $sess) and $result;
				}
			}
		}
                upgrade_mod_savepoint(true, 2008021904, 'attforblock');
	}

    if ($oldversion < 2008102401) {
    	
        $table = new xmldb_table('attforblock');
        
        $field = new xmldb_field('grade');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100', 'name');
        $dbman->add_field($table, $field);
    	
        
        $table = new xmldb_table('attendance_sessions');
        
        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $dbman->change_field_unsigned($table, $field);
    	
//        $field = new xmldb_field('creator');
//        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'courseid');
//        change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('sessdate');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'creator');
        $dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('duration');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'sessdate');
        $dbman->add_field($table, $field);
        
        $field = new xmldb_field('timetaken');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'takenby');
        $dbman->change_field_unsigned($table, $field);
    	$dbman->rename_field($table, $field, 'lasttaken');

        $field = new xmldb_field('takenby');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'lasttaken');
        $dbman->change_field_unsigned($table, $field);
        $dbman->rename_field($table, $field, 'lasttakenby');
    	
        $field = new xmldb_field('timemodified');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'lasttaken');
        $dbman->change_field_unsigned($table, $field);
    	
        
    	$table = new xmldb_table('attendance_log');
        
        $field = new xmldb_field('attsid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
    	$dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('studentid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'attsid');
    	$dbman->change_field_unsigned($table, $field);
    	
    	$field = new xmldb_field('statusid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'status');
    	$dbman->add_field($table, $field);
    	
        $field = new xmldb_field('statusset');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null, null, null, null, 'statusid');
        $dbman->add_field($table, $field);
    	
        $field = new xmldb_field('timetaken');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'statusid');
    	$dbman->add_field($table, $field);
    	
        $field = new xmldb_field('takenby');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'timetaken');
    	$dbman->add_field($table, $field);
    	
        //Indexes
        $index = new xmldb_index('statusid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('statusid'));
    	$dbman->add_index($table, $index);
    	
        $index = new xmldb_index('attsid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('attsid'));
        $dbman->drop_index($table, $index);
    	
        $field = new xmldb_field('attsid'); //Rename field
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $dbman->rename_field($table, $field, 'sessionid');
        
        $index = new xmldb_index('sessionid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('sessionid'));
        $dbman->add_index($table, $index);
        
    	
    	$table = new xmldb_table('attendance_settings');
        
        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('visible');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'grade');
        $dbman->add_field($table, $field);
        
        $field = new xmldb_field('deleted');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'visible');
        $dbman->add_field($table, $field);
        
        //Indexes
        $index = new xmldb_index('visible');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('visible'));
        $dbman->add_index($table, $index);
        
        $index = new xmldb_index('deleted');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('deleted'));
        $dbman->add_index($table, $index);
        
    	$dbman->rename_table($table, 'attendance_statuses');

        upgrade_mod_savepoint(true, 2008102401, 'attforblock');
    }
    
    if ($oldversion < 2008102406) {
    	
	    if ($courses = $DB->get_records_sql("SELECT courseid FROM {attendance_sessions} GROUP BY courseid")) {
	    		foreach ($courses as $c) {
	    			//Adding own status for course (now it must have own)
	    			if (!$DB->count_records('attendance_statuses', array( 'courseid'=> $c->courseid))) {
	    				$statuses = $DB->get_records('attendance_statuses', array('courseid'=> 0));
						foreach($statuses as $stat) {
							$rec = $stat;
							$rec->courseid = $c->courseid;
							$DB->insert_record('attendance_statuses', $rec);
						}
	    			}
	    			$statuses = $DB->get_records('attendance_statuses', array('courseid'=> $c->courseid));
	    			$statlist = implode(',', array_keys($statuses));
	    			$sess = $DB->get_records_select_menu('attendance_sessions', "courseid = ? AND lasttakenby > 0", array($c->courseid));
	    			$sesslist = implode(',', array_keys($sess));
					foreach($statuses as $stat) {
						execute("UPDATE {attendance_log}
										SET statusid = {$stat->id}, statusset = '$statlist'
									  WHERE sessionid IN ($sesslist) AND status = '$stat->status'");
					}
	    			$sessions = $DB->get_records_list('attendance_sessions',  array('id'=> $sesslist));
					foreach($sessions as $sess) {
						execute("UPDATE {attendance_log}
										SET timetaken = {$sess->lasttaken}, 
											takenby = {$sess->lasttakenby}
									  WHERE sessionid = {$sess->id}");
					}
	    			
	    		}
	    	}
                upgrade_mod_savepoint(true, 2008102406, 'attforblock');
    	    	
     }
     
    if ($oldversion < 2008102409) {
        $table = new xmldb_table('attendance_statuses');
        
        $field = new xmldb_field('status');
        $dbman->drop_field($table, $field);
        
        $index = new xmldb_index('status');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('status'));
        $dbman->drop_index($table, $index);

        
        $table = new xmldb_table('attendance_log');
        
        $field = new xmldb_field('status');
        $dbman->drop_field($table, $field);
        
        $index = new xmldb_index('status');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('status'));
        $dbman->drop_index($table, $index);
        
        $table = new xmldb_table('attendance_sessions');

        $field = new xmldb_field('creator');
        $dbman->drop_field($table, $field);
        upgrade_mod_savepoint(true, 2008102409, 'attforblock');
        
    } 

    if ($oldversion < 2010070900) {
        $table = new xmldb_table('attendance_sessions');

        $field = new xmldb_field('groupid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'courseid');
        $dbman->add_field($table, $field);

        $index = new xmldb_index('groupid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('groupid'));
        $dbman->add_index($table, $index);
        upgrade_mod_savepoint(true, 2010070900, 'attforblock');
    }

    if ($oldversion < 2010123003) {

        $table = new xmldb_table('attendance_sessions');

        $field = new xmldb_field('attendanceid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'groupid');
        $dbman->add_field($table, $field);

        $index = new xmldb_table('attendanceid');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('attendanceid'));
        $dbman->add_index($table, $index);

        $sql = "UPDATE {attendance_sessions} AS ses,{attforblock} AS att SET ses.attendanceid=att.id WHERE att.course=ses.courseid";
        $dbman->execute($sql);

        $table = new xmldb_table('attendance_statuses');

        $field = new xmldb_field('attendanceid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'courseid');
        $dbman->add_field($table, $field);
        $index = new xmldb_index('attendanceid');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('attendanceid'));
        $dbman->add_index($table, $index);

        $sql = "UPDATE {attendance_statuses} AS sta,{attforblock} AS att SET sta.attendanceid=att.id WHERE att.course=sta.courseid";
        execute($sql);
    }
    return $result;
}

?>
