<?php

namespace Database\Seeders;

use App\Models\CategoryTreatment;
use App\Models\Coupon;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Gender;
use App\Models\IdentificationType;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\SchedulesHoursMinute;
use App\Models\TypeTreatment;
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
            'name' => 'Otro'
        ]);
//        Gender::factory()->count(1)->create([
//            'name' => 'No reporta género'
//        ]);

        /*=============================================
             CREAMOS LOS TIPOS DE DOCUMENTO
         =============================================*/
        IdentificationType::factory()->count(1)->create([
            'name' => 'DNI (España)'
        ]);
        IdentificationType::factory()->count(1)->create([
            'name' => 'NIE (España)'
        ]);
        IdentificationType::factory()->count(1)->create([
            'name' => 'NIF (España)'
        ]);
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
            'name' => 'Plan Diamante',
            'currency' => 'EUR',
            'description' => 'Seguimiento semanal Online 10€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 10,
            'user_id' => 1,
            'number_appointments' => 0,
            'period' => 'week',
            'image_background' => '/assets/images/plans/plan-diamante.png'
        ]);
        Plan::factory()->count(1)->create([
            'name' => 'Plan Esmeralda',
            'currency' => 'EUR',
            'description' => '2 citas online de 30 minutos 60€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 80,
            'user_id' => 1,
            'number_appointments' => 2,
            'time_interval_appointments' => 15,
            'period' => 'month',
            'image_background' => '/assets/images/plans/plan-esmeralda.png'
        ]);
        Plan::factory()->count(1)->create([
            'name' => 'Plan Rubí',
            'currency' => 'EUR',
            'description' => '4 citas online de 30 minutos 120€ Acceso a Nuestra Plataforma Online: Podrás ver tus ejercicios y valoración cuantas veces quieras 20€',
            'price' => 140,
            'user_id' => 1,
            'number_appointments' => 4,
            'time_interval_appointments' => 7,
            'period' => 'month',
            'image_background' => '/assets/images/plans/plan-rubi.png'
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

        /*=============================================
            CREAMOS LAS CATEGORIAS DE LOS TRATAMIENTOS
        =============================================*/
        CategoryTreatment::factory()->count(1)->create([
            'name' => 'Hombre',
        ]);
        CategoryTreatment::factory()->count(1)->create([
            'name' => 'Mujer',
        ]);
        CategoryTreatment::factory()->count(1)->create([
            'name' => 'Pareja',
        ]);
        CategoryTreatment::factory()->count(1)->create([
            'name' => 'Infante',
        ]);
        /*=============================================
            CREAMOS TRATAMIENTOS
        =============================================*/
        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Mejora tu Postura',
            'description' => '¿Tienes mala postura?
¿Te ves el abdomen distendido y sin fuerza?
¿Te duelen las cervicales y la espalda?
¿Tienes constante dolor lumbar?
Quiero ayudarte, junt@s trabajaremos por tu bienestar, la terapia consiste en hacer liberación muscular para aliviar el dolor, haremos ejercicios de tonificación y fortalecimiento en tu Complejo Abdominal + Lumbar + Suelo Pélvico, después los trabajaremos en sinergia con los otros músculos para conseguir su equilibrio y estabilidad, esto te ayudará a mantenerte con buena postura en tu día a día.
El tratamiento lo puedes hacer desde cualquier lugar del mundo, desde la comodidad de tu hogar o si sales de viaje allí estaremos a tu lado, tan solo con un click en cualquier dispositivo electrónico ONLINE, o si lo prefieres PRESENCIAL, primero haremos una valoración corporal y después tendrás tus ejercicios en nuestra plataforma online, tu recuperación la conseguiremos con 7 minutos de ejercicio al día, porque considero que un buen ejercicio que cumpla tus requisitos funcionales puede generarte GRANDES CAMBIOS A CORTO PLAZO...
¡VAMOS HACERLO! Junt@s conseguiremos tu cambio :)'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['1', '2', '3', '4']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Embarazo/Preparto/Postparto',
            'description' => 'Eres única y auténtica, por eso necesitas un tratamiento personalizado.
En el Embarazo y Preparto haremos una valoración abdominoperineal y postural, seguido haremos un plan de tratamiento con tus deseos y con los resultados de tu valoración; también haremos un Plan B, un tratamiento pensando en circunstancias que no podemos controlar.
En el Posparto trabajaremos la recuperación y el fortalecimiento de la zona
Abdominal + Lumbar + Suelo Pélvico, para las demandas físicas que requiere esta etapa.
Estos tratamientos los puedes hacer desde la comodidad de tu hogar ONLINE, o si lo prefieres PRESENCIAL, primero haremos una valoración corporal, después tendrás tus ejercicios en nuestra plataforma online, nuestras pautas de tratamiento son de 7 minutos al día, queremos la efectividad ya que el proceso de adaptación en esta etapa puede ser demandante, por eso te queremos acompañar con tan solo 7 minutos de ejercicio al día para conseguir tu RECUPERACIÓN.
Tratamientos personalizados:
Prevención y recuperación de diástasis abdominal, masaje terapéutico, tratamiento para la incontinencia, dolor de espalda, ciática, activación abdominoperienal, ejercicios de kegel, masaje perineal, pujo fisiológico, epino, piernas cansadas y retención de líquidos, pubalgia o dolor en el pubis, reflujo, dolor abdominal, preparación al parto, sensación de pesadez vaginal, ejercicios funcionales personalizados, ligamento redondo, hipopresivos, electroterapia, Indiba, Método 5P, meditaciones y danza.'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Cáncer. Rehabilitación Funcional',
            'description' => 'Queremos acompañarte en el proceso y en tu recuperación.
Los objetivos son restaurar la funcionalidad de los tejidos, atenuar posibles secuelas y mejorar tu calidad de vida.
Lo haremos en equipo tu y yo, trabajaremos la movilidad, la elasticidad y la tonificación de las zonas irradiadas, previo al tratamiento haremos una valoración para conocer
el estado de tu cuerpo.
El tratamiento lo puedes hacer desde la comodidad de tu hogar ONLINE o PRESENCIAL, cada pauta terapéutica la tendrás en nuestra plataforma online, siempre estaremos conectad@s para lograr los objetivos de tu recuperación.'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Diástasis Abdominal/Hernia Abdominal/Inguinal Pre-Post Cirugía Abd',
            'description' => '¿Sientes que tu abdomen se hincha y se pone duro?
¿Te levantas con el abdomen plano y en la noche te ves un barrigón?
¿Quieres mejorar la funcionalidad y la estética de tu abdomen?
¿Sientes debilidad abdominal?
¿Tras el parto sientes que tu abdomen perdió fuerza, te notas el abdomen blando?
¿Te sobresale un bulto en el abdomen cuando te incorporas?
¿Te gustaría tener el abdomen plano?
¿Te van a operar del abdomen y no sabes por donde empezar?
Estoy para ayudarte, te daré tratamiento personalizado para tu recuperación,  trabajaremos en equipo, puedes tener distensión, hinchazón, debilidad de la musculatura abdominal y diástasis que es la distensión del transverso abdominal y rectos abdominales.
Para rehabilitarte tenemos que ver si existen intolerancias ya que puede ser una causa, si existen o no intolerancias hay que hacer un tratamiento para conseguir un equilibrio entre tensión y distensión.
Tus músculos abdominales se mueven en distintos ejes corporales, siendo protagonistas y antagonistas al movimiento que quieres realizar, por eso es tan importante una valoración detallada de cada músculo, ligamento y fascia, los valoraremos en reposo y en activación.
El tratamiento consiste en hacer ejercicio analítico en los músculos débiles, este ejercicio es muy muy personalizado porque buscamos tu mejor respuesta de activación funcional, una vez recuperada la zona, haremos ejercicios evolutivos dinámicos que impliquen la vida diaria y ejercicios posturales para darle equilibrio a las fuerzas musculares.
Haremos ejercicio con aparatología para aumentar el tono, la fuerza y el colágeno, nuestras pautas de tratamiento te llevarán 7 minutos de ejercicio al día, lograremos tu recuperación siendo efectiv@s en las pautas que te daré.
El tratamiento lo puedes hacer desde la comodidad de tu hogar ONLINE o PRESENCIAL, cada pauta terapéutica la tendrás en nuestra plataforma online, siempre estaremos conectad@s para lograr los objetivos de tu recuperación.'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Fortalecimiento Abdominal y del Suelo Pélvico',
            'description' => 'Este tratamiento consiste en mejorar la funcionalidad Abdominal y del Suelo Pélvico, aumentando su tono, fuerza y resistencia.
Con el tratamiento obtendrás como beneficios:
- La prevención del dolor de espalda, dolor dorsal y cervical,
- Activación de tu centro corporal para mantener una postura correcta (CALPP)
- Prevención de patologías del suelo pélvico: Incontinencias, prolapsos, relajación vaginal y disfunciones sexuales en la mujer y el hombre.
- Aumento del placer, la sensibilidad y la respuesta sexual.
El tratamiento lo puedes hacer desde cualquier lugar del mundo, desde la comodidad de tu hogar y si sales de viaje allí estaremos a tu lado, tan solo con un click en cualquier dispositivo electrónico ONLINE, o si lo prefieres PRESENCIAL., previo al fortalecimiento haremos una valoración de la musculatura Abdominal y del Suelo Pélvico, con los resultados de tu valoración haremos nuestro plan de tratamiento, considerando tus deseos y los objetivos que quieres conseguir con este tratamiento.
El ejercicio que te daré te llevará 7 minutos al día, es personalizado y lo tendrás en nuestra plataforma online.'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Quiero mi Abdomen Plano',
            'description' => 'Este tratamiento consiste en mejorar la funcionalidad Abdominal y del Suelo Pélvico, aumentando su tono, fuerza y resistencia.
Con el tratamiento obtendrás como beneficios:
- La prevención del dolor de espalda, dolor dorsal y cervical,
- Activación de tu centro corporal para mantener una postura correcta (CALPP)
- Prevención de patologías del suelo pélvico: Incontinencias, prolapsos, relajación vaginal y disfunciones sexuales en la mujer y el hombre.
- Aumento del placer, la sensibilidad y la respuesta sexual.
El tratamiento lo puedes hacer desde cualquier lugar del mundo, desde la comodidad de tu hogar y si sales de viaje allí estaremos a tu lado, tan solo con un click en cualquier dispositivo electrónico ONLINE, o si lo prefieres PRESENCIAL., previo al fortalecimiento haremos una valoración de la musculatura Abdominal y del Suelo Pélvico, con los resultados de tu valoración haremos nuestro plan de tratamiento, considerando tus deseos y los objetivos que quieres conseguir con este tratamiento.
El ejercicio que te daré te llevará 7 minutos al día, es personalizado y lo tendrás en nuestra plataforma online.'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['4', '2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Recuperación Abdominoperineal para Disfunciones',
            'description' => 'Abdominales/SueloPélvico Prolapso/Hemorroides Anorgasmia/Estreñimiento Histerectomía/ Eyaculación Precoz/Disfunción Eréctil/Pre-Post Cirugía Perineal
Vejiga Hiperactiva/ Perdidas de: Orina/Fecal/Gases
Próstata: Adenoma/Hipertrofia Benigna'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Tratamientos Multidisciplinares',
            'description' => 'Láser Ginecológico/Cirugía Íntima/Indiba/Infiltraciones/Ondas de Choque'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        TypeTreatment::factory()->count(1)->create([
            'treatment' => 'Stop Dolor Abdominoperineal',
            'description' => 'Dolor Abdominal/Dolor Durante la Relación Sexual
Síndrome Miofascial/Dolor Pélvico Crónico/ Dolor Vulvar/Prostatitis/Síndrome Pudendal/Síndrome Genitourinario de la menopausia/Prostatitis
Endometriosis/Dolor Menstrual/Sequedad Vaginal
Dispareunia/Vaginismo/ Atrofia Vulvovaginal/Liquen Escleroso/Inflamación y Procesos Tensiónales del Suelo Pélvico'
        ])->each(function (TypeTreatment $typeTreatment) {
            $typeTreatment->categories()->attach(['2', '1', '3']);
        });

        /*=============================================
            CREAMOS DOCTOR UNO
        =============================================*/
        User::factory()->count(1)->create([
            'name' => 'Mailyn',
            'last_name' => 'Solarte',
            'email' => 'developer.mauricio2310@gmail.com',
            'phone' => '+34 675176612',
            'slug' => Str::slug('Mailyn' . '-' . 'Solarte' . '-' . Str::random(8), '-')
        ])->each(function (User $user) {
            Doctor::factory()->count(1)->create([
                'zoom_api_key' => 'kcrItEiYRyaAeeli7ongAA',
                'zoom_api_secret' => 'NpxY33l8R40SpI7RxdgjZvdcth7vIwHo0EfG',
                'user_id' => $user->id,
                'biography' => 'Fisioterapeuta Especialista en Suelo Pélvico, con más de 15 años de experiencia, mi rehabilitación abdominoperineal, es para la mujer, hombre e infante, la rehabilitación es a través del autoconocimiento, utilizando la sinergia, el movimiento funcional, la personalización del ejercicio y su evolución, son mis claves para solucionar las alteraciones posturales, dolor cervical/dorsal/lumbar y las disfunciones abdominales y el suelo pélvico.',
            ])->each(function (Doctor $doctor) {
                $doctor->treatments()->attach(['1', '2']);

                $doctorScheduleOne = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2022-12-27',
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                       'doctor_schedule_id' => $doctorSchedule->id,
                       'hour' => '10',
                       'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '11',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '14',
                        'minute' => '30'
                    ]);
                });

                $doctorScheduleTwo = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2022-12-30'
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '13',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '15',
                        'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '16',
                        'minute' => '30'
                    ]);
                });

                $doctorScheduleThree = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2023-01-20'
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '08',
                        'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '11',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '14',
                        'minute' => '30'
                    ]);
                });

            });
        });
        /*=============================================
            CREAMOS DOCTOR DOS
        =============================================*/
        User::factory()->count(1)->create([
            'name' => 'Maria',
            'last_name' => 'Yanez',
            'email' => 'smgutierrez@unimayor.edu.co',
            'phone' => '+34 675176612',
            'slug' => Str::slug('Maria' . '-' . 'Yanez' . '-' . Str::random(8), '-')
        ])->each(function (User $user) {
            Doctor::factory()->count(1)->create([
                'zoom_api_key' => '',
                'zoom_api_secret' => '',
                'user_id' => $user->id,
                'biography' => 'Odontóloga graduada de la Universidad del Valle con 7 años de experiencia en el sector.
Realizó sus estudios de especialización en Rehabilitación Oral en la Universidad Nacional de Colombia, Bogotá, donde estuvo vinculada como docente de clínica en el pregrado de la Facultad.
Apasionada por el trabajo y la atención a pacientes.',
            ])->each(function (Doctor $doctor) {
                $doctor->treatments()->attach(['1', '2', '3', '4']);

                $doctorScheduleOne = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2022-1-29'
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '10',
                        'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '11',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '14',
                        'minute' => '30'
                    ]);
                });

                $doctorScheduleTwo = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2022-11-30'
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '13',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '15',
                        'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '16',
                        'minute' => '30'
                    ]);
                });

                $doctorScheduleThree = DoctorSchedule::factory()->count(1)->create([
                    'doctor_id' => $doctor->id,
                    'date' => '2022-12-02'
                ])->each(function (DoctorSchedule $doctorSchedule) {
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '08',
                        'minute' => '30'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '11',
                        'minute' => '00'
                    ]);
                    SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => '14',
                        'minute' => '30'
                    ]);
                });

                /*=============================================
                    CREAMOS LOS CUPONES DE DESCUENTO
                 =============================================*/
                Coupon::factory()->count(1)->create([
                    "name" => 'AmorSaludWom',
                    "discount" => 10,
                    "description" => 'Este cupon es para uso solo en tiempos de febrero en amor y amistad',
                    "create_user_id" => 1,
                    "date_expiration" => "2022-12-15",
                    "limit_use" => 2
                ]);
            });
        });
    }
}
