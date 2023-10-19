-- DROP TABLE "groups";
-- DROP TABLE "reports";
-- DROP TABLE "settings";
-- DROP TABLE "controllers";
-- DROP TABLE "timezones";
-- DROP TABLE "doors";
-- DROP TABLE "users";
-- DROP TABLE "rules";
-- VACUUM;

-- TODO Nette Catch als db of table mist=> Fatal error: Uncaught PDOException: SQLSTATE[HY000]: General error: 1 no such table: reports in /maasland_app/www/lib/db.php:21 Stack trace: #0 /maasland_app/www/lib/db.php(21): PDO->prepare('SELECT * FROM `...') #1 /maasland_app/www/lib/model.report.php(4): find_objects_by_sql('SELECT * FROM `...') #2 /maasland_app/www/controllers/main.php(12): find_reports() #3 /maasland_app/www/lib/limonade.php(388): report_index() #4 /maasland_app/www/index.php(160): run() #5 {main} thrown in /maasland_app/www/lib/db.php on line 21
-- empty tall catch, alleen bij users, doors, 

CREATE TABLE "groups" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  "name" VARCHAR,
  "updated_at" DATETIME,"created_at" DATETIME DEFAULT CURRENT_TIMESTAMP  );

CREATE TABLE "reports" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  "door" VARCHAR NOT NULL,
  "user" VARCHAR NOT NULL,
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );

CREATE TABLE "settings" ( "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  "name" VARCHAR NOT NULL,"value" VARCHAR NOT NULL,"type" INTEGER,
  "title" TEXT, 
  "status" INTEGER,
  "updated_at" DATETIME,"created_at" DATETIME DEFAULT CURRENT_TIMESTAMP);

CREATE TABLE "controllers" ( "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
  "name" VARCHAR, 
  "reader_1" INTEGER, 
  "reader_2" INTEGER, 
  "button_1" INTEGER, 
  "button_2" INTEGER, 
  "sensor_1" INTEGER, 
  "sensor_2" INTEGER, 
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );

CREATE TABLE "timezones" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  "name" VARCHAR,
  "start" DATETIME,
  "end" DATETIME,
  "weekdays" TEXT DEFAULT "0,1,2,3,4,5,6",
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );

CREATE TABLE "doors" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
  "name" VARCHAR NOT NULL , 
  "controller_id" INTEGER,
  "timezone_id" DATETIME,
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );

-- Table users
--  keycode is unique
CREATE TABLE "users" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , 
  "name" VARCHAR,
  "last_seen" DATETIME,
  "remarks" TEXT,
  "group_id" INTEGER, 
  "keycode" TEXT, 
  "max_visits" INTEGER, 
  "start_date" DATETIME, 
  "end_date" DATETIME, 
  "visit_count" INTEGER,
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );
CREATE UNIQUE INDEX keycode_unique_index ON users(keycode);

-- Table rules
--  door_id,group_id is unique
CREATE TABLE "rules" ( "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  "name" VARCHAR,
  "group_id" id NOT NULL,
  "door_id" id NOT NULL,
  "timezone_id" id NOT NULL, 
  "updated_at" DATETIME, "created_at"  DATETIME DEFAULT CURRENT_TIMESTAMP  );
CREATE UNIQUE INDEX unique_group_door ON rules(door_id,group_id);
