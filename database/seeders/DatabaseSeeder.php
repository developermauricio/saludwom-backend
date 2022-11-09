<?php

namespace Database\Seeders;

use App\Models\Gender;
use App\Models\IdentificationType;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        /*=============================================
             CREAMOS CARPETAS EN EL STORAGE
         =============================================*/
        Storage::deleteDirectory('patient');
        Storage::deleteDirectory('users');
        Storage::deleteDirectory('archives');
        Storage::makeDirectory('patient');
        Storage::makeDirectory('users');
        Storage::makeDirectory('archives');

        /*=============================================
             CREAMOS CIUDADES Y PAÍSES DEL MUNDO
         =============================================*/
        $path = 'database/seeders/sql/ciudadesPaises.sql';
        DB::unprepared(file_get_contents($path));

        /*=============================================
             CREAMOS GÉNEROS
         =============================================*/
        Gender::factory()->count(1)->create([
            'name' => 'Masculino'
        ]);
        Gender::factory()->count(1)->create([
            'name' => 'Femenino'
        ]);
        Gender::factory()->count(1)->create([
            'name' => 'Otros'
        ]);
        Gender::factory()->count(1)->create([
            'name' => 'No reporta género'
        ]);

        /*=============================================
             CREAMOS LOS TIPOS DE DOCUMENTO
         =============================================*/
        IdentificationType::factory()->count(1)->create([
            'name' => 'Cédula de Ciudadanía'
        ]);
        IdentificationType::factory()->count(1)->create([
            'name' => 'Cédula de Extranjería'
        ]);
        IdentificationType::factory()->count(1)->create([
            'name' => 'Pasaporte'
        ]);

        /*=============================================
             CREAMOS LOS ROLES
         =============================================*/
        $administrator = Role::create(['name' => 'Administrator']);
        $patient = Role::create(['name' => 'Patient']);
        $doctor = Role::create(['name' => 'Doctor']);

        /*=============================================
            CREAMOS UN USUARIO ADMINISTRADOR
        =============================================*/
       $admin = User::factory()->count(1)->create([
            'name' => 'Admin',
            'last_name' => 'Salud Wom',
            'email' => 'admin@saludwom.com'
        ])->each(function (User $user) use ($administrator) {
            $user->roles()->attach($administrator->id); // Asignamos el rol administrador al usuario
        });

        /*=============================================
            CREAMOS TRES PLANES
        =============================================*/
        Plan::factory()->count(1)->create([
            'name' => 'Plan Rubí',
            'currency' => 'EUR',
            'description' => 'Seguimiento semanal Online 10€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 30.25,
            'user_id' => 1,
            'number_appointments' => 0,
            'period' => 'week',
            'image_background' => '/assets/images/plans/plan-rubi.png'
        ]);
        Plan::factory()->count(1)->create([
            'name' => 'Plan Esmeralda',
            'currency' => 'EUR',
            'description' => '2 citas online de 30 minutos 60€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 80,
            'user_id' => 1,
            'number_appointments' => 2,
            'period' => 'month',
            'image_background' => '/assets/images/plans/plan-esmeralda.png'
        ]);
        Plan::factory()->count(1)->create([
            'name' => 'Plan Diamante',
            'currency' => 'EUR',
            'description' => '4 citas online de 30 minutos 120€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 140,
            'user_id' => 1,
            'number_appointments' => 4,
            'period' => 'year',
            'image_background' => '/assets/images/plans/plan-diamante.png'
        ]);
        /*=============================================
            CREAMOS PACIENTE UNO
        =============================================*/
        User::factory()->count(1)->create([
            'name' => 'Silvio Mauricio',
            'last_name' => 'Gutierrez Quiñones',
            'email' => 'silviotista93@gmail.com',
            'phone' => '+57 3154940483',
            'slug' => Str::slug('Silvio Mauricio' . '-' . 'Gutierrez Quiñones' . '-' . Str::random(8), '-')
        ])->each(function (User $user) {
            Patient::factory()->count(1)->create([
                'user_id' => $user->id,
                'gender_id' => 1,
                'patient_type' => 'client'
            ]);
        });
    }
}
