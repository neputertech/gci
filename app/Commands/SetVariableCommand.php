<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};

class SetVariableCommand extends Command
{

    const SET_DEV_PROTECTED = false;
    const SET_STAGING_PROTECTED = true;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create';
//                             {env=all : dev,staging,production,all}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create Gitlab CI Variables';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

//        $env = $this->argument('env');

        $private_token = $this->ask('Enter Private Token:', config('vars.private_token'));

        $project_id = $this->ask('Enter Project ID:');

        $env = $this->choice('Enter environment scope', ['dev', 'staging', 'production', 'all'], 'all');

        $pre_environment_keys = [
            'dev' => 'DEV',
            'staging' => 'STAGE',
            'production' => 'PROD'
        ];

        $environment_keys = $pre_environment_keys;

        if($env != "all") {
            $environment_keys = Arr::only($pre_environment_keys, $env);
        }

        if(count($environment_keys) == 0) {
            $this->error('Error creating gitlab variables. Please try again.');
            die;
        }

        $new_arr = [];
        foreach ($environment_keys as $environment_key_ => $environment_key) {

            $set_protected = true;
            if ($environment_key == 'DEV') {
                $set_protected = self::SET_DEV_PROTECTED;
            } elseif ($environment_key == 'STAGE') {
                $set_protected = self::SET_STAGING_PROTECTED;
            }

            foreach (config('vars.var_key_names') as $var_key_name) {
                $new_arr[$environment_key . '_' . $var_key_name] = [
                    "variable_type" => "env_var",
                    "key" => $environment_key . '_' . $var_key_name,
                    "value" => "",
                    "protected" => $set_protected,
                    "masked" => false,
                    "environment_scope" => $environment_key_,
                ];
            }
        }

        $final_key_val_arr = array_merge([  // prepend default variable
            'CUSTOM_GLOBAL_RUNNER_TAG_NAME' => [
                "variable_type" => "env_var",
                "key" => "CUSTOM_GLOBAL_RUNNER_TAG_NAME",
                "value" => "",
                "protected" => false,
                "masked" => false,
                "environment_scope" => "*"
            ],
        ], $new_arr);

        $this->info("Initializing...");
        $url = "https://gitlab.com/api/v4/projects/" . $project_id . "/variables";

        render(<<<'HTML'
            <div class="py-1 ml-2">
                <em class="ml-1">
                  Creating Variables...
                </em>
            </div>
        HTML);

        foreach ($final_key_val_arr as $key => $final_key_val_arr_single) {

            $response = Http::withHeaders([
                'PRIVATE-TOKEN' => $private_token,
                'Content-Type' => 'application/json',
            ])->post($url, $final_key_val_arr_single);

            $this->info($key.': Status: '.$response->status());

            if($response->status() == 401)
                break;

            sleep(2);
        }

        render(<<<'HTML'
            <div class="py-1 ml-2">
                <em class="ml-1">
                  Creating Variables... Complete
                </em>
            </div>
        HTML);

        $this->notify("Gitlab CI", "Creating Variables... Complete", "./gitlab-logo.svg");

    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
