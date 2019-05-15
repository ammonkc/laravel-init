<?php

namespace Ammonkc\AppSetupCmd\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Contracts\Role as RoleContract;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
                            {--admin : Indicates whether the user should be an admin}
                            {--super-admin : Indicates whether the user should be a super-admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Holds the user information.
     *
     * @var array
     */
    protected $userData = [
        'first_name' => null,
        'last_name'  => null,
        'email'      => null,
        'password'   => null,
    ];

    /**
     * validation rules.
     *
     * @var array
     */
    protected $rules = [
        'first_name'    => ['required', 'string', 'max:255'],
        'last_name'     => ['required', 'string', 'max:255'],
        'email'         => ['required', 'email', 'unique:users,email', 'string', 'max:255'],
        'password'      => ['required', 'min:8', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/', 'string'],
    ];


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
        $this->askUserFirstName();
        $this->askUserLastName();
        $this->askUserEmail();
        $this->askUserPassword();

        $this->createUser();
    }

    /**
     * Asks the user for the first name.
     *
     * @return void
     * @todo   Use the Laravel Validator
     */
    protected function askUserFirstName()
    {
        do {
            // Ask the user to input the first name
            $first_name = $this->ask('Please enter your first name: ');

            // Check if the first name is valid
            if ($first_name == '') {
                // Return an error message
                $this->error('Your first name is invalid. Please try again.');
            }

            // Store the user first name
            $this->userData['first_name'] = $first_name;
        } while (! $first_name);
    }

    /**
     * Asks the user for the last name.
     *
     * @return void
     * @todo   Use the Laravel Validator
     */
    protected function askUserLastName()
    {
        do {
            // Ask the user to input the last name
            $last_name = $this->ask('Please enter your last name: ');

            // Check if the last name is valid.
            if ($last_name == '') {
                // Return an error message
                $this->error('Your last name is invalid. Please try again.');
            }

            // Store the user last name
            $this->userData['last_name'] = $last_name;
        } while (! $last_name);
    }

    /**
     * Asks the user for the user email address.
     *
     * @return void
     * @todo   Use the Laravel Validator
     */
    protected function askUserEmail()
    {
        do {
            // Ask the user to input the email address
            $email = $this->ask('Please enter your user email: ');

            // Check if email is valid
            if ($email == '') {
                // Return an error message
                $this->error('Email is invalid. Please try again.');
            }

            // Store the email address
            $this->userData['email'] = $email;
        } while (! $email);
    }

    /**
     * Asks the user for the user password.
     *
     * @return void
     * @todo   Use the Laravel Validator
     */
    protected function askUserPassword()
    {
        do {
            // Ask the user to input the user password
            $password = $this->secret('Please enter your user password: ');

            // Check if email is valid
            if ($password == '') {
                // Return an error message
                $this->error('Password is invalid. Please try again.');
            }

            // Store the password
            $this->userData['password'] = $password;
        } while (! $password);
    }

    /**
     * Create new user
     *
     * @return user
     * @todo   Use the Laravel Validator
     */
    protected function createUser()
    {
        $userData = array_merge($this->userData, [
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'activated' => true,
        ]);

        $validator = Validator::make($userData, $this->rules);

        if ($validator->fails()) {
            $this->info('User not created. See error messages below:');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return false;
        }

        $model = config('auth.providers.users.model');
        $user = $model::create($userData);

        $roleClass = app(RoleContract::class);
        $superAdmin = $roleClass::findOrCreate('super-admin');
        $admin = $roleClass::findOrCreate('admin');


        if ($this->option('super-admin')) {
            $user->assignRole($superAdmin->id);
        }

        if ($this->option('admin')) {
            $user->assignRole($admin->id);
        }

        $this->info("User {$userData['first_name']} {$userData['last_name']} has been created.");

        return $user;
    }
}
