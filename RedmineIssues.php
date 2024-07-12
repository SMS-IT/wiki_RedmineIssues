<?php

$wgExtensionFunctions[] = 'RedmineIssuesSetupExtension';
$wgExtensionCredits['parserhook'][] = array(
    'name' => 'RedmineIssues',
    'author' => 'Parfenov Sergey'
);

function RedmineIssuesSetupExtension() {
    global $wgHooks;

    $wgHooks['OutputPageBeforeHTML'][] = "RedmineIssues_BeforeHTML";
    return true;
}

function ShowIssues(&$text)
{
    global $wgTitle;
    if ($wgTitle == null) return true;

    if (isset($_GET['printable']) && $_GET["printable"] == "yes") return true;

    if (strpos($text, "__NOREDMINE__")) {
        $text = str_replace("__NOREDMINE__", "", $text);
        return true;
    }

    $title1 = $wgTitle->getPartialURL();
    $title1 = "index.php/" . $title1;

    $title2 = $wgTitle->getDBkey();
    $title2 = "index.php/" . $title2;

    $text = "<script src='http://code.jquery.com/jquery-1.12.4.min.js'></script>
                <script>
                    var rdIssues = '';
                    function issTimeout(divname, disptype) {
						var issInterval = setInterval(function(divname, disptype) {
                            if (document.getElementById(divname)) {
								document.getElementById('loader').style.display = 'none';
                                clearInterval(issInterval);
                                if (document.getElementById(divname).style.display == disptype)
                                    document.getElementById(divname).style.display = 'none';
                                else
                                    document.getElementById(divname).style.display = disptype;
                            }
                        }, 100, divname, disptype);
                    }

                    function showdiv(divname, disptype) {
						if (rdIssues === '') {
							rdIssues = 1;
                            redmine_issues(false);
						}
						var issuesDiv = document.getElementById(divname);
						if (!issuesDiv || issuesDiv.style.display == 'none') {
							document.getElementById('loader').style.display = 'inline-block';
						}
						issTimeout(divname, disptype);
                    }
                </script>
                <div id='RedmineIssues'><div class='RedmineIssuesLoading'></div></div>
                <script type=text/javascript>
                    function redmine_issues(count) {
                        var url = ''; // ссылка к links_to_wiki_count.php, например https://wiki.ru/project/links_to_wiki_count.php
                        if (count) url +='?count=set'
                        $.ajax({
                            type: 'GET',
                            url: url,
                            data: { title1: '$title1', title2:'$title2' },
                            dataType: 'html',
                            success: function( data ) { if (rdIssues === '') {
                                document.getElementById('RedmineIssues').innerHTML = data;
                            } else {
                                document.getElementById('RedmineIssues').innerHTML += data;
								}
                            }
                        });
                    }

                    redmine_issues(true);
      </script>" . $text;

    return true;
}

function RedmineIssues_ParserAfterTidy(&$parser, &$text)
{
    ShowIssues($text);
    return true;
}

function RedmineIssues_BeforeHTML(&$out, &$text)
{
    ShowIssues($text);
    return true;
}

?>
