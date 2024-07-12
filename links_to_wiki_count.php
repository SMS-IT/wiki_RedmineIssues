<?php
	header('Access-Control-Allow-Origin: *');

	require("RIMysqlConnector.php");

	$text = "";

	$redmine_dbserver = "";
	$redmine_dbuser = "";
	$redmine_database = "";
	$redmine_dbpassword = "";
	$redmine_dbencoding = "utf8";
	$redmine_issue = "";

	try {
		$issues = "";
		$errors = "";

		$title1 = $_GET['title1'];
		$title2 = $_GET['title2'];
		$title2x = $title2;
		$title2x = str_replace('(', '[(]', $title2x);
		$title2x = str_replace(')', '[)]', $title2x);

		if (isset($_GET['alwaysShow']))
		    $alwaysShow = $_GET['alwaysShow'];
		else
		    $alwaysShow = "";

		$rm_connector = new RIMysqlConnector(
		    $redmine_dbserver, $redmine_dbuser, $redmine_database, $redmine_dbpassword, $redmine_dbencoding);

    	$query_body = "
            FROM " . $redmine_database . ".issues i
       	";

    	$query_join = "
            JOIN (" . $redmine_database . ".trackers t, " . $redmine_database . ".issue_statuses s)
      	        ON ( t.id = i.tracker_id AND s.id = i.status_id  )
            LEFT OUTER JOIN " . $redmine_database . ".users u
            	ON i.assigned_to_id = u.id
        ";

    	$query_where = "
            WHERE
                (i.haswiki > 0)
                AND
                (
                    (
                        (i.description like '%$title1%' OR i.description like '%$title2%')
						
                        /*AND
                        i.description RLIKE '($title1|$title2x)($|[ \#\n\r\t\s)]+.*$)'*/
                    )
                    OR
                    (
                        EXISTS (
                            SELECT 1
                            FROM " . $redmine_database . ".journals j
                            WHERE j.journalized_id = i.id AND
                            (j.notes LIKE '%$title1%' OR j.notes LIKE '%$title2x%')
							
                            /*AND
                            j.notes RLIKE '($title1|$title2x)($|[ \#\n\r\t)]+.*$)'*/
                    )
                )
            )
        ";

		$getCount = isset($_GET['count']);
		
		if (! $getCount) {
			$query = "SELECT i.id, i.subject, t.name as tracker, s.name as status, i.status_id as status_id, u.lastname as user " . $query_body . $query_join . $query_where;
		} else {
			$query = "SELECT COUNT(*) as iss_count " . $query_body . $query_where;
		}

		file_put_contents('/tmp/sql.log', $query . '\n', FILE_APPEND);
		// $errors .= $query;

		$dbres = $rm_connector->execute($query);
		$taskcnt = 0;
		if ($dbres) {
			while ($row = $rm_connector->fetch($dbres)) {
				if ($getCount) {
					$taskcnt = $row["iss_count"];
				} else {
					$issues .= sprintf("<tr class='st_%s'><td>%s</td> <td>%s</td> <td><a href='$redmine_issue/%s'>%s</a></td> <td>%s</td></tr>\n",
					  $row["status_id"], $row["tracker"], $row["status"], $row["id"], $row["subject"], $row["user"]);
				}
			}
			$rm_connector->free($dbres);
		} else {
			$errors .= "DB Error: " . $rm_connector->getDbError($rm_connector->db) . " | " . $rm_connector->getError();
		}

		// если были ошибки то выводим только ошибки
		if ($errors != "")
			$text = "<div style='padding: 2px 5px; margin: 5px 0px; border:1px solid red;'>$errors</div>\n" . $text;
		else

		// если спрашивали колво задач и задачи есть - выводим плашку
		if ( ($getCount) && ($taskcnt != 0)) {
			$text = "
			<style>
				#loader {
					display: none;
					padding: 0.5em 1em;
					background: #92c8e9;
					position: relative;
					border-radius: 35px;
					font-size: large;
				}
				.rmright {
					display: inline-block;
					margin-top: -70px;  margin-right: -15px;
					padding: 5px 10px 5px 15px;
					float:right; clear:both;
					background:#CECB18; 
					border-top-left-radius: 15px;
					border-bottom-left-radius: 15px;
				}
				.rmright a {
					font-weight: bold; color:#FFFFFF; font-size:120%; 
				}
				.rmhide { display: none; }
				tr.st_5 td { background-color:rgb(224, 224, 224); color:green !important; font-style:italic;
			</style>
			" . 
			(($alwaysShow != "") ?  "" :  "<div class='rmright'><a onclick=\"showdiv('rmtasks', 'inline-block');\" id='href_rmtasks'>Задачи ($taskcnt)</a></div><div id='loader'><p>Загрузка задач...</p></div>");
		}
		else

		// если запрпшивали сами задачи и они есть - выводим их
		if ($issues != "") {
			$text = "
				<style>
					div.rm { 
						padding: 6px 10px 0px 10px; margin: 0 0 10px 0; 
						background:#ffffdd; 
						" . 
						(($alwaysShow != "") ? "" : "display:none;") . "
					}
					table.rm { border-spacing:0px; background-color:transparent; 
						margin: 0px -10px 0px -10px;
					}
					table.rm tbody tr:nth-child(odd) { background-color:#fafab6; }
					table.rm tr td { padding:2px 15px 2px 2px; color: #909090; }
					table.rm tr td:first-child { padding-left:20px !important; }
					table.rm tr td:last-child { padding-right:20px !important; }
					b.rm { font-weight: bold; color:#CECB18; font-size:120%; margin-bottom: 5px; display: inline-block; }
				</style>
				
				<div id='rmtasks' class='rm'>
					<b class='rm'>Задачи:</b><br>
					<table class='rm'> $issues </table>
				</div>";
		}

	} catch (Exception $e) {
		$text = "<div style='border: 1px solid black;'>исключение: " . $e->getMessage() . "</div>\n" . $text;
	}

	echo $text;
?>

