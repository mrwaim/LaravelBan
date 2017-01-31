<?php

namespace Klsandbox\LaravelBan;

use Illuminate\Console\Command;

use Symfony\Component\Console\Helper\ProgressBar;

class LaravelBan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:banned-keywords {keyword?} {--mode=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check banned keyword from being used on files inside specified directory';

    /**
     * show warning , if it true it will print the warning later
     * @var bool
     */
    private $show_warning;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->check_config();

        $keyword = $this->argument('keyword');

        $rules_count = sizeof(config('banned-keywords'));

        for ($i=0; $i < $rules_count; $i++) { 
            $this->ban_searcher($i , $keyword);
        }


        if ($this->show_warning) {
            $response_mode = $this->option('mode') == 'strict' ? 'error' : 'info' ;
            $this->$response_mode("\nSeems like these file(s) contain banned words!");
        } else {
             $this->info("\n All files are clean. ");
        }
    }


    /**
     * check if the config has been publish or not
     * @param  array $config the array inside the config
     * @return array         the array inside the config
     */
    private function check_config()
    {
        if (is_null(config('banned-keywords'))) {
            $this->error("\n Please publish the config first, using php artisan vendor:publish ");
            exit();
        }
    }


    /**
     * search the banned keyword
     * @param  int   $rule_id   the rule id on the config
     * @param  string $keyword  the keyword need to be fine, if null will search the keyword form the config
     * @return void
     */
    private function ban_searcher($rule_id , $keyword = null)
    {
        $directories = config("banned-keywords.$rule_id.folders");
        $keywords = config("banned-keywords.$rule_id.keywords");

        if ($keyword) {
            $keywords = [$keyword];
        }

        foreach ($directories as $directory) {
            $this->run_directory($directory, $keywords , $rule_id);
        }
    }


    /**
     * run through the specified directory
     * @param  string $directory the directory need to be search
     * @param  array  $keywords  keyword need to be search
     * @param  int   $rule_id   the rule id on the config
     * @return void            
     */
    private function run_directory($directory , array $keywords, $rule_id)
    {
        
        $files = $this->get_searchable_files($directory);
            
        $files = $this->exclude_file($files, $rule_id);

        foreach ($files as $file) {
                $data = $this->find_keyword( $directory.$file , $keywords);
                $this->show_table($data , config("banned-keywords.$rule_id.name"));  
        }
    }


    /**
     * get the file that searchabele
     * @param  string $directory the path need to be search
     * @return array             the name list of file available
     */
    private function get_searchable_files($directory)
    {
        $files = [];
        if ($directory) {
            foreach (new \DirectoryIterator($directory) as $file) {
              if ($file->isFile()) {
                  $files[] = $file->getFilename();
              }
            }
        }
        return $files;
    }

    /**
     * this should exclude file(s) that didnt need to be search for keyword(s) within the directory
     * @param  array $file_list original file list need to be search
     * @param  int   $rule_id   the rule id on the config
     * @return array            the file list that has been filtered
     */
    private function exclude_file($file_list, $rule_id)
    {
        $files = config("banned-keywords.$rule_id.excepts");
        if ($files) {
            foreach ($files as $file) {
                unset($file_list[array_search($file, $file_list)]);  
            }
        }

        return $file_list;
    }

    /**
     * find the keywords within the file
     * @param  string $file_path the full path of the file need to be search
     * @param  array  $keywords  list of keyword need to be search
     * @return array             the result of the search
     */
    private function find_keyword($file_path , array $keywords)
    {
        $file = file_get_contents($file_path);
        $result = [];
        foreach ($keywords as $keyword) {
            if( strpos($file, $keyword) !== false) {
                $count = substr_count($file, $keyword);
                $result[] = [ 'keyword'=> $keyword , 'count' => $count , 'file' => $file_path];
            }
        }

        return $result;
    }

    /**
     * display the data as table, if available 
     * @param  array  $data the data from search 
     * @return void
     */
    private function show_table(array $data , $rule_name)
    {
        if ($data) {
            $this->info("\n $rule_name");
            $this->info("\n".$data[0]['file']);
            $headers = ['Keywords', 'Count'];

            for ($i=0; $i < count($data) ; $i++) { 
                unset($data[$i]['file']); //for aesthetic purpose, we remove this from the table
            }

            $this->table($headers, $data); 

            $this->show_warning = true;
        }

    }
}
