<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'id' => '1a1d2b12-d3b5-436d-aa94-762ab8f5fbd4',
                'employee_id' => '22ff1b14-2301-4a27-a022-6736b3c9f318', // Jerson Marg Cerezo
                'email' => 'jerson.cerezo.100@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$cAb2RxCG6sbrZbQwBriRHuWNc.SyZ5.EjqgdA8SkvikEY7VvPrWta',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => Carbon::parse('2025-10-09 15:35:44'),
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:33:26'),
                'updated_at' => Carbon::parse('2025-10-09 15:35:44'),
            ],
            [
                'id' => '22470036-f3dc-45cb-b896-2f03dfd007b1',
                'employee_id' => '4a189262-87df-48a6-8155-97fbe315e830', // Balingka Kalat
                'email' => 'jersondev03@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$UpzOqjgMBkCoXGrlmKGajOwXAy2b56Ms5YH8JRWIYOCuyn0.zJIBK',
                'role' => 'hr',
                'is_active' => 1,
                'last_login_at' => Carbon::parse('2025-10-19 12:57:54'),
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-09 15:44:09'),
                'updated_at' => Carbon::parse('2025-10-19 12:57:54'),
            ],
            [
                'id' => '25c3fd0a-81ba-49da-b8d1-5aa651526640',
                'employee_id' => '75282d62-51f2-41a9-a5e3-6b4b6838a5f7', // Alexander Estares
                'email' => 'alex@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$EW9uf2NI1Mu4fQK5aaDQy.3jCA0R5BlTSaT9YE.XoqGQH/2ojwUdy',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:37:41'),
                'updated_at' => Carbon::parse('2025-10-05 15:37:41'),
            ],
            [
                'id' => '2a95e71a-c36d-49c9-b626-aad8784ff219',
                'employee_id' => '0c6fcadb-5d42-453e-b85e-04df54d4b42b', // Charlie Cawile
                'email' => 'charlie@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$EbuiItNU7/r5Tc.X3GW.6OB3NROGaSAXQTJ/axVnrd0E5.A.i12Wm',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:39:54'),
                'updated_at' => Carbon::parse('2025-10-05 15:39:54'),
            ],
            [
                'id' => '384266c3-1888-47ed-b160-26db176eb639',
                'employee_id' => '41c930c9-40d9-4e8b-9575-87e1ae3c8ab7', // Bulbasaur Poke
                'email' => 'Bulbasaur@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$3ixECeCmeie9NuwfGGsxjuRx75wnkNK818RapTAqpDkK5ZEci748K',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 17:09:28'),
                'updated_at' => Carbon::parse('2025-10-05 17:09:28'),
            ],
            [
                'id' => '634b2deb-8337-48b1-984e-099231e88a66',
                'employee_id' => 'c413f98e-d91b-4c61-b4d4-eb00e40a34b1', // Reece Bibaro
                'email' => 'reece@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$0FKRTtI5EftqVgQ9XHZwoOrPM2LkLksWdu4kxjiszO6PF2pRIoNCu',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:40:28'),
                'updated_at' => Carbon::parse('2025-10-05 15:40:28'),
            ],
            [
                'id' => '847400e2-d2ef-4144-a0f2-d61bd030be11',
                'employee_id' => 'b19c96de-91d9-4bcd-a0b6-2010bc7dae8c', // Lowegie Raga
                'email' => 'lowegie@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$itWmwPsYorGR1wqU1seaXeBlkHLBOiah2DDyTcE3R9Npa5kfJi4Gy',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:36:36'),
                'updated_at' => Carbon::parse('2025-10-05 15:36:36'),
            ],
            [
                'id' => '9ef7954c-a2e0-4030-812f-898779f663ae',
                'employee_id' => 'b5aecc58-69dc-4d2c-b435-d8f13a9416ef', // Maria Sampalok
                'email' => 'maria@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$WSqBcovKm.cNsXBNY9wuUO6jJ6IhMTsKgPHyJnEH1oBkddaVum3by',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 17:08:31'),
                'updated_at' => Carbon::parse('2025-10-05 17:08:31'),
            ],
            [
                'id' => 'b9015c8c-348f-4793-98b2-5d3ac3dd2aa9',
                'employee_id' => 'b5b39e80-36cc-4f35-abaf-20272f5a461c', // Curt Vincent Guiling
                'email' => 'curt@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$fFwHjV0Fw1l1xVoHYUGDn.Dn5cGN59UGTH/fpIGbd16N8HX5dLb9q',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:39:11'),
                'updated_at' => Carbon::parse('2025-10-05 15:39:11'),
            ],
            [
                'id' => 'dd397e44-a108-4f0d-a808-659b5608c3da',
                'employee_id' => '243e7606-9542-4c71-867c-05bea5e658d3', // Reyven Plaza
                'email' => 'reyven@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$jNoAoo03XPN7lwpj.EFK6OgTZGUR2tRPZHS2RvBwTyKny13XK5R26',
                'role' => 'employee',
                'is_active' => 1,
                'last_login_at' => null,
                'remember_token' => null,
                'created_at' => Carbon::parse('2025-10-05 15:41:03'),
                'updated_at' => Carbon::parse('2025-10-05 15:41:03'),
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }

        $this->command->info('Accounts created successfully!');
    }
}