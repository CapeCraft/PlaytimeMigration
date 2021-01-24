<?php

    require 'vendor/autoload.php';

    use Medoo\Medoo;
    use Dotenv\Dotenv;
    use Symfony\Component\Yaml\Yaml;

    class PlaytimeMigrate {

        private static $database;

        /**
         * Initialise stuff
         *
         * @return void
         */
        private static function initialise() {
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            self::$database = new Medoo([
                'database_type' => 'mysql',
                'database_name' => $_ENV['DATABASE_NAME'],
                'server' => 'localhost',
                'username' => $_ENV['DATABASE_USERNAME'],
                'password' => $_ENV['DATABASE_PASSWORD']
            ]);
        }

        /**
         * Start the migration
         *
         * @return void
         */
        public static function startMigration() {
            //Initialise stuff
            self::initialise();

            //Get all files and a current status
            $files = glob($_ENV['ABSO_PATH'] . "/*.yml");
            $total = count($files);
            $current = 1;

            //Loop through all files
            foreach($files as $filename) {
                //Load the yaml file
                $yaml = Yaml::parse(file_get_contents($filename));

                //Set all the variables
                $uuid = str_replace(".yml", "", basename($filename));
                $username = $yaml['username'];
                $playtime = $yaml['playtime'] * 60;

                //Insert into the database
                self::$database->insert('player_afk_data', [
                    'uuid' => $uuid,
                    'playtime' => $playtime
                ]);

                //Delete the file
                if(filter_var($_ENV['DELETE_FILE'], FILTER_VALIDATE_BOOL)) {
                    unlink($filename);
                }

                //Output it
                echo "$current/$total - ";
                echo "$uuid - $username - $playtime\n";
                $current++;
            }

            echo "\n\nALL DONE!!!!";
        }

    }

    PlaytimeMigrate::startMigration();
