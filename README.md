#### Плагин используется для показа на странице статьи списка задач из Redmine, в описании или комментариях которых присутствует ссылка на статью в вики

## Установка 

### Добавьте следующую строку в LocalSettings.php
```
require_once "$IP/extensions/RedmineIssues/RedmineIssues.php";
```

### В директорию /extensions/RedmineIssues/RedmineIssues.php поместите файл RedmineIssues.php

### Используйте Apache или другой веб-сервер, чтобы можно было сделать запрос из MediaWiki к файлу links_to_wiki_count.php

### Заполните данные для доступа к базе данных в inks_to_wiki_count.php
```
	$redmine_dbserver = "";
	$redmine_dbuser = "";
	$redmine_database = "";
	$redmine_dbpassword = "";
	$redmine_dbencoding = "utf8";
	$redmine_issue = "";
```

### Вставьте ссылку к файлу links_to_wiki_count.php в RedmineIssues.php
```
    #65 var url = ''; // ссылка к links_to_wiki_count.php, например https://wiki.ru/project/links_to_wiki_count.php
```
