<?php 

    class FactController {
        private $db;

        function __construct() {
            $this->db = new Flux();
        }

        public static function index() {
            try {
                $facts = Fact::All()::Exec();

                if(!$facts) {
                    NotFound('No facts found');
                }

                Ok($facts);
            }
            catch(Exception $e) {
                throw new Error($e);
            }
        }

        public static function show($id) {
            try {
                $fact = Fact::FindOne($id)::Exec();

                if(!$fact) {
                    NotFound('No fact found');
                }

                Ok($fact);
            }
            catch(Exception $e) {
                throw new Error($e);
            }
        }

        public static function create() {
            try {
                $fact = Fact::ModelData();

                Fact::InsertObject($fact, 'Fact');
                Ok($fact);
            }
            catch(Exception $e) {
                throw new Error($e);
            }
        }

        public static function destroy($id) {
            try {
                Fact::Delete($id)::Exec();
                Ok('Fact deleted');
            }
            catch(Exception $e) {
                throw new Error($e);
            }
        }
    }
