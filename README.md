= ebook library =

This software will create a database of all epubs found recursively from one directory onwards with author, title, language and file path

it will offer the possibility to browse through the database or search for author or title.


create table books (
    id INT NOT NULL auto_increment primary key,
    author varchar(500),
    title varchar(500),
    path varchar(500),
    UNIQUE KEY(path)
)
alter table books ADD FULLTEXT(author, title);



need the download.php script
