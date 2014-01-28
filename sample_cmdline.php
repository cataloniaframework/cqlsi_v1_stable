<?php

 /**
 * Creator:      Carles Mateo
 * Date Created: 2014-01-27 11:26
 * Last Updater: Carles Mateo
 * Last Updated: 2014-01-27 14:10
 * Filename:     sample_cmdline.php
 * Description:  Sample to invoke with php -f sample_cmdline.php
 */

require_once 'datetime.class.php';
require_once 'security.class.php';
require_once 'file.class.php';
require_once 'db.class.php';

use CataloniaFramework\Db as Db;

define('TMP_ROOT', '/tmp/');
define('LOG_SQL_TO_FILE', false);

$st_db_config = Array(	'read'  => Array(   'servers'   => Array(0 => Array('connection_type'   => Db::TYPE_CONNECTION_CASSANDRA_CQLSI,
                                                                            'connection_method' => Db::CONNECTION_METHOD_TCPIP,
                                                                            'server_hostname'   => '127.0.0.1',
                                                                            'server_port'		=> Db::PORT_DEFAULT_CASSANDRA,
                                                                            'username'			=> 'www_cassandra',
                                                                            'password'			=> 'passCassandra',
                                                                            'database'			=> 'cataloniafw',
                                                                            'client_encoding'   => 'utf8'
                                                                            )
                                                                )
                                        ),
                        'write' => Array(   'servers'   => Array(0 => Array('connection_type'   => Db::TYPE_CONNECTION_CASSANDRA_CQLSI,
                                                                            'connection_method' => Db::CONNECTION_METHOD_TCPIP,
                                                                            'server_hostname'   => '127.0.0.1',
                                                                            'server_port'		=> Db::PORT_DEFAULT_CASSANDRA,
                                                                            'username'			=> 'www_cassandra',
                                                                            'password'			=> 'passCassandra',
                                                                            'database'			=> 'cataloniafw',
                                                                            'client_encoding'   => 'utf8'
                                                                            )
                                                                )

                                        )


                    );

// select * from system.schema_keyspaces;
// describe keyspaces

$o_db = new Db($st_db_config);

// Instead of:
//$s_cql = 'DESCRIBE KEYSPACES';
// That returns no data use:
$s_cql = 'select * from system.schema_keyspaces;';


$st_results = $o_db->queryRead($s_cql);

$b_catalonia_keyspace_found = false;

if ($st_results['result']['error'] > 0) {
    // There was error, for example
    // Connection error: Could not connect to localhost:9160
    // or Bad Request: Keyspace 'cataloniafw' does not exist
    echo 'The query: '.$st_results['result']['query'].' returned error: '.$st_results['result']['error_description']."\n";
} else {
    echo 'The query: '.$st_results['result']['query'].' returned data: '."\n";
    print_r($st_results['data']);

    foreach($st_results['data'] as $i_key=>$st_values) {
        if ($st_values['keyspace_name'] == 'cataloniasample') {
            $b_catalonia_keyspace_found = true;
            break;
        }
    }

    if ($b_catalonia_keyspace_found == false) {
        echo "cataloniasample keyspace not found, creating it...\n";

        $s_cql = "CREATE KEYSPACE cataloniasample
                  WITH replication = {'class':'SimpleStrategy', 'replication_factor':1};";

        // This is for doing admin queries without selecting a keyscape
        $o_db->setUseDatabaseOrKeyspace(false);
        $st_results = $o_db->queryWrite($s_cql);

        // Future queries will use the keyspace
        $o_db->setUseDatabaseOrKeyspace(true);

        if ($st_results['result']['error'] > 0) {
            echo 'The query: '.$st_results['result']['query'].' returned error: '.$st_results['result']['error_description']."\n";
            print_r($st_results);
        } else {
            echo 'Keyspace cataloniasample created successfully!'."\n";
        }
    }
}
