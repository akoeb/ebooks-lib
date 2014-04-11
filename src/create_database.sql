
--  This file is part of the ebooks-library software
--  Copyright (C) 2014, Alexander Köb <nerdkram@koeb.me>
-- 
--  Licensed under the GNU General Public License version 3.
--  See the COPYING file for a full license statement.


create table if not exists books (
    id INT NOT NULL auto_increment primary key,
    author varchar(500),
    title varchar(500),
    language varchar(100),
    path varchar(255)
 -- that does not work :-(   
 --   ,UNIQUE KEY(path)
) engine MyISAM DEFAULT CHARSET=utf8;
alter table books ADD FULLTEXT txtidx(author, title);
alter table books ADD FULLTEXT txtidx_aut(author);
alter table books ADD FULLTEXT txtidx_tit(title);



