#!/usr/bin/env python3

import sqlite3
import sys
from sqlite3 import Error


def create_connection(db_file):
    conn = None
    try:
        conn = sqlite3.connect(db_file)
    except Error as e:
        print(e)

    return conn

def drop_reports(conn):
    print('Drop reports')
    cur = conn.cursor()
    
    cur.execute("DELETE FROM reports;")
    print(f'lines removed {conn.total_changes}')
    conn.commit()

def vacuum(conn):
    cur = conn.cursor()
    print('Vacuum')
    cur.execute("vacuum;")
    #print(f'vacuum = {conn.total_changes}\n')
    conn.commit()

def change_settings_time(conn):
    print('CHANGE add time to settings')
    cur = conn.cursor()
    
    cur.execute("UPDATE settings SET name='time', value='', type='9', title='Time', status='1', updated_at='2023-11-11 11:11:11' WHERE id = 5")
    print(f'lines changed {conn.total_changes}')
    conn.commit()

def main(argv):
    database = 'test.flexess'
    if len(sys.argv) > 1:
        database = sys.argv[1]

    print(f'Opening: {database}')
    # database = r"test.flexess"
    # create a database connection
    conn = create_connection(database)
    print('Open DB')
    with conn:
        print('start operations')
        drop_reports(conn)
        vacuum(conn)
        change_settings_time(conn)
        print('finish operations')

if __name__ == '__main__':
    main(sys.argv)
