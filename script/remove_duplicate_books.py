#!/usr/bin/python
# quick hack to remove duplicate lines.
# Please do not complain about the slowness of this script,
# I know it taks hours because it executes one delete with every
# duplication. 
# This script was only a quick hack and I did not want to bother 
# with smarter solutions. you have been warned.
# 
# that being said, if you want to use the script, fill in the database 
# connection stuff and shoot.
# on debian based systems, the python package for mysql is called

import MySQLdb
import time
from sets import Set

# database stuff

# FILL IN THESE VALUES:

# database host
db_host = ""
# user name in mysql
db_user = ""
# mysql user password
db_passwd = ""
# the database name
db_name = ""
# the table with the books
db_table = ""


# connect
try:
    db = MySQLdb.connect(host=db_host, user=db_user, passwd=db_passwd,db=db_name)
    
    cursor = db.cursor()
    db.autocommit(True)
    
    starttime = time.time()

    # get the first id of all collections of unique books (that is the one we intent to keep)
    cursor.execute("select min(id) from %s group by path, author, title, language" % db_table)
    
    runtime = time.time() - starttime

    # read that into a set for easy access with "NOT IN"
    keep = Set([])
    for row in cursor.fetchall():
        keep.add(row[0])
   
    print "Found %s elements to keep (Runtime %ss)" % (len(keep), runtime)

    # we iterate ove rall records of the db:
    starttime = time.time()
    cursor.execute("SELECT id from %s" % db_table)
    runtime = time.time() - starttime
    all = cursor.fetchall()
    
    print "Found %s elements in total (Runtime %ss)" % (len(all), runtime)

    # delete query has a prepared stmt variable for the id
    delete_query = "DELETE FROM %s where id = %%s" % db_table
    
    # now loop over all books, checking if we want to keep the id and firing a delete if not
    for row in all:
        if row[0] not in keep:
            cursor.execute(delete_query, (row[0]))
    
# catch db errors:
except MySQLdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)
                
finally:      
    if db:    
        db.close()
    
