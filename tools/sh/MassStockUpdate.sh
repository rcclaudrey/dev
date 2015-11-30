#### VARIABLES  ######
HOST=localhost;
USER=use name;
PASSWORD=user password;
PATH=path to the sql file;
FILE=file;
#### IMPORT  ######
mysql -h $HOST -u $USER -p$PASSWORD $DB < $PATH/$FILE.sql;
rm $PATH/$NAME.sql;
