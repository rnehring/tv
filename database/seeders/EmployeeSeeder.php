<?php

namespace Database\Seeders;

use App\Models\EmployeeAnniversary;
use App\Models\EmployeeBirthday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample August data (from the legacy dev_tv export) so the slideshow shows real
 * content immediately. Real months arrive via the CSV import in the admin.
 */
class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // [day, First, Last]
        $birthdays = [
            [2, 'Trey', 'Andrus'], [2, 'Alejandro', 'Montoya'], [4, 'Marell', 'Allen'],
            [5, 'Carlos', 'Villafranco'], [6, 'Amanda', 'Dudek'], [6, 'Christian', 'Ramos Caraballo'],
            [6, 'Albert', 'Pearson'], [7, 'Jose', 'Tecun'], [7, 'Elizabeth', 'Leon'],
            [8, 'Aidan', 'Vire'], [8, 'Kristina', 'Visscher'], [9, 'Lesli', 'Nelson'],
            [10, 'Leidiana', 'Nunez Perez'], [10, 'Marco', 'Martinez'], [11, 'Tiburcio', 'Chavez'],
            [12, 'Reynaldo', 'Rodriguez'], [14, 'Danilo', 'Contreras'], [14, 'Kenneth', 'Parsons'],
            [15, 'Patrick', 'Williams'], [16, 'Gordon', 'Henderson'], [18, 'Scott', 'Cascaden'],
            [20, 'Hailee', 'Hinerman'], [20, 'Willy', 'Castellano'], [23, 'Elias', 'Villarreal'],
            [23, 'Rolando', 'Lopez Martinez'], [25, 'Joseph', 'Lara'], [27, 'Dalia', 'Sanchez'],
            [30, 'Armando', 'Paulin'], [31, 'Suneel', 'Bikki'],
        ];

        foreach ($birthdays as [$day, $first, $last]) {
            EmployeeBirthday::updateOrCreate(
                ['source_key' => 'b:'.Str::slug($first.'-'.$last).":8:{$day}"],
                ['first_name' => $first, 'last_name' => $last, 'month' => 8, 'day' => $day, 'imported_on' => now()->toDateString()]
            );
        }

        // [First, Last, hireDate]
        $anniversaries = [
            ['Joseph', 'Ngekeda', '2023-08-01'], ['Jesus', 'Tobon', '2025-08-01'], ['Samantha', 'Wilkinson', '2023-08-02'],
            ['Willy', 'Castellano', '2025-08-04'], ['Mahmoud', 'Mouheiche', '2025-08-04'], ['Ralph', 'Mallozzi', '2022-08-08'],
            ['Matthew', 'Teunessen', '2010-08-09'], ['Paula', 'Crawford', '2020-08-10'], ['Yeinier', 'Castillo Torres', '2025-08-11'],
            ['Lesli', 'Nelson', '2024-08-12'], ['Rosa', 'Huerta-Toledo', '2024-08-12'], ['Gregory', 'Robinson', '2002-08-12'],
            ['Leidiana', 'Nunez Perez', '2024-08-13'], ['Ellen', 'Turner', '2023-08-14'], ['Jerry', 'McCollum', '2022-08-14'],
            ['Tiburcio', 'Chavez', '2018-08-14'], ['Francis', 'Carabellese', '2023-08-14'], ['Timothy', 'Medine', '2016-08-15'],
            ['Manuel', 'Vazquez Guerra', '2023-08-15'], ['Gabriel', 'Joseph', '2023-08-15'], ['Brandon', 'VanLiere', '2021-08-16'],
            ['Deshawn', 'Key', '2010-08-17'], ['Steven', 'Scherer', '2025-08-18'], ['Kent', 'Taylor', '2023-08-21'],
            ['Jeremy', 'Miller', '2015-08-24'], ['Darwin', 'Tudare Nava', '2025-08-25'], ['Joshua', 'Sonier', '2011-08-29'],
            ['Benjamin', 'Mueller', '2022-08-29'], ['Jacob', 'Hill', '2022-08-29'], ['Shatara', 'Portis', '2021-08-30'],
            ['Ines', 'Ekinovic', '2021-08-30'], ['Joeldys', 'Clara Pino', '2023-08-30'], ['Nathan', 'Orlikowski', '2023-08-31'],
            ['Albert', 'Pearson', '2020-08-31'], ['Antonio', 'Mendoza', '2020-08-31'],
        ];

        foreach ($anniversaries as [$first, $last, $hire]) {
            $day = (int) date('j', strtotime($hire));
            EmployeeAnniversary::updateOrCreate(
                ['source_key' => 'a:'.Str::slug($first.'-'.$last).":8:{$day}"],
                ['first_name' => $first, 'last_name' => $last, 'month' => 8, 'day' => $day, 'hire_date' => $hire, 'imported_on' => now()->toDateString()]
            );
        }
    }
}
