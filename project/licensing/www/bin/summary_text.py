#!/usr/bin/env python3
#
#   ____                                               _____         _   
#  / ___| _   _ _ __ ___  _ __ ___   __ _ _ __ _   _  |_   _|____  _| |_ 
#  \___ \| | | | '_ ` _ \| '_ ` _ \ / _` | '__| | | |   | |/ _ \ \/ / __|
#   ___) | |_| | | | | | | | | | | | (_| | |  | |_| |   | |  __/>  <| |_ 
#  |____/ \__,_|_| |_| |_|_| |_| |_|\__,_|_|   \__, |   |_|\___/_/\_\\__|
#                                              |___/                     
#
import os
import json
import sys
import re
import datetime
import numpy as np
import pandas as pd
import sqlalchemy as sa


def get_connection():
    # load database credentials and change database to "datalabeling"
    db_creds = json.load(open(f"/var/www/html/surveys/licensing/.dbcredentials.json"))
    connection_uri = '/'.join( db_creds['default_uri'].split('/')[:3] + ['datalabeling'])

    # create connection
    conn = sa.engine.create_engine(connection_uri)

    # test if connection works
    # q = """
    # SELECT NOW() AS jetzt
    # """
    # display(pd.read_sql(q, conn))
    return conn

def get_text(conn, html_format=True):
    paragraph = []
    q = """
    SELECT COUNT(assignment_id) AS n_assignments
    FROM licensing_assignments
    """
    assignments_df = pd.read_sql(q, con=conn)
    total_number_of_assignments = assignments_df.n_assignments[0]

    q = """
    SELECT CAST(submit_time AS DATETIME) AS submit_time,
        CAST(display_time AS DATETIME) AS display_time,
        UNIX_TIMESTAMP(submit_time) - UNIX_TIMESTAMP(display_time) AS t_delta, 
        doc_id, user_id, assignment_id, license_type, license_roles, valid_response
    FROM licensing_responses
    WHERE user_id<>'anonymous'
    """
    responses_df = pd.read_sql(q, con=conn)
    # print(f"Number of records: {responses_df.shape[0]:,}")
    valid_responses_df = responses_df[responses_df['valid_response']==1].copy()
    # print(f"Number of valid responses: {valid_responses_df.shape[0]:,}")

    total_number_of_assignments = assignments_df.n_assignments[0]
    total_number_of_respones = responses_df.shape[0]
    total_number_of_valid_respones = valid_responses_df.shape[0]
    start_date = responses_df.submit_time.min()
    time_sec_10per, time_sec_median, time_sec_90perc = \
        np.round(np.percentile(valid_responses_df.t_delta.dropna(), [10, 50, 90]))

    hours_since_start = (datetime.datetime.now()-start_date).total_seconds()/3600
    hour_rate = total_number_of_respones / hours_since_start
    hours_to_do = (total_number_of_assignments - total_number_of_respones)/hour_rate
    completed_by = (datetime.datetime.now() + datetime.timedelta(hours=hours_to_do)).strftime('%A, %B %d, %Y after %Hh')

    paragraph.append(f"""
    At this time {total_number_of_respones:,} of {total_number_of_assignments:,} samples have been labeled.
    In {(total_number_of_respones-total_number_of_valid_respones):,} cases the user was unable to evaluate the sample.
    """)

    paragraph.append(f"""
    The median time to complete labeling a sample is {time_sec_median:,} seconds. 
    Whereby 90% of samples were evealuated in less than {time_sec_90perc:,} seconds.
    """)

    paragraph.append(f"""
    At the current rate this labeling task will be completed by {completed_by}.</p>
    """
    )

    if html_format: 
        return '\n'.join([ f'<p class="analysis-summary">{x}</p>' for x in paragraph ])
    else:
        return '\n'.join(paragraph)


if __name__ == '__main__':
    conn = get_connection()
    text = get_text(conn)
    print(text)

