<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;
use Illuminate\Database\Seeder;

class AcademicCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            // [name, short_name, calendar_type]
            ['University of Dhaka', 'DU', 'bi'],
            ['Bangladesh University of Engineering and Technology', 'BUET', 'bi'],
            ['Dhaka University of Engineering & Technology', 'DUET', 'bi'],
            ['Jahangirnagar University', 'JU', 'bi'],
            ['Jagannath University', 'JnU', 'bi'],
            ['Bangladesh University of Professionals', 'BUP', 'tri'],
            ['National University, Bangladesh', 'NU', 'bi'],
            ['Bangladesh Open University', 'BOU', 'bi'],
            ['Islamic University of Technology', 'IUT', 'tri'],
            ['Daffodil International University', 'DIU', 'tri'],
            ['North South University', 'NSU', 'tri'],
            ['BRAC University', 'BRACU', 'tri'],
            ['Independent University, Bangladesh', 'IUB', 'tri'],
            ['American International University-Bangladesh', 'AIUB', 'tri'],
            ['East West University', 'EWU', 'tri'],
            ['United International University', 'UIU', 'tri'],
            ['Ahsanullah University of Science and Technology', 'AUST', 'bi'],
            ['University of Asia Pacific', 'UAP', 'tri'],
            ['Stamford University Bangladesh', 'SUB', 'tri'],
            ['Southeast University', 'SEU', 'tri'],
            ['University of Liberal Arts Bangladesh', 'ULAB', 'tri'],
            ['Green University of Bangladesh', 'GUB', 'tri'],
            ['State University of Bangladesh', 'SUBD', 'tri'],
            ['Northern University Bangladesh', 'NUB', 'tri'],
            ['Primeasia University', 'PAU', 'tri'],
            ['Bangladesh University of Business and Technology', 'BUBT', 'tri'],
            ['Uttara University', 'UU', 'tri'],
            ['City University', 'CityU', 'tri'],
            ['World University of Bangladesh', 'WUB', 'tri'],
            ['Manarat International University', 'MIU', 'tri'],
            ['International University of Business Agriculture and Technology', 'IUBAT', 'tri'],
            ['Eastern University', 'EU', 'tri'],
            ['Presidency University', 'PU', 'tri'],
            ['Canadian University of Bangladesh', 'CUB', 'tri'],
            ['BGMEA University of Fashion & Technology', 'BUFT', 'tri'],
            ['Shanto-Mariam University of Creative Technology', 'SMUCT', 'tri'],
            ['University of Information Technology & Sciences', 'UITS', 'tri'],
            ['Bangladesh University', 'BU', 'tri'],
            ['Atish Dipankar University of Science and Technology', 'ADUST', 'tri'],
            ['Gono Bishwabidyalay', 'GB', 'bi'],
            ['Central Women\'s University', 'CWU', 'bi'],
            ['Asian University of Bangladesh', 'AUB', 'tri'],
            ['Dhaka International University', 'DhIU', 'tri'],
            ['IBAIS University', 'IBAIS', 'tri'],
            ['Prime University', 'PrimeU', 'tri'],
            ['Royal University of Dhaka', 'RUD', 'tri'],
            ['Millennium University', 'MU', 'tri'],
            ['Sonargaon University', 'SU', 'tri'],
            ['Varendra University', 'VU', 'tri'],
            ['European University of Bangladesh', 'EUB', 'tri'],
        ];

        foreach ($universities as [$name, $short, $calendar]) {
            University::updateOrCreate(
                ['name' => $name],
                ['short_name' => $short, 'calendar_type' => $calendar, 'is_custom' => false]
            );
        }

        $faculties = [
            'Faculty of Science',
            'Faculty of Engineering',
            'Faculty of Business & Economics',
            'Faculty of Arts & Humanities',
            'Faculty of Social Sciences',
            'Faculty of Law',
            'Faculty of Medicine',
            'Faculty of Pharmacy',
            'Faculty of Agriculture',
            'Faculty of Education',
            'Faculty of Fine Arts',
            'Faculty of Information Technology',
            'School of Business',
            'School of Engineering & Technology',
            'School of Science & Technology',
            'School of Liberal Arts & Social Sciences',
            'School of Law',
            'School of Health Sciences',
        ];

        foreach ($faculties as $name) {
            Faculty::updateOrCreate(['name' => $name], ['is_custom' => false]);
        }

        $departments = [
            'Computer Science and Engineering',
            'Software Engineering',
            'Electrical and Electronic Engineering',
            'Electronics and Telecommunication Engineering',
            'Civil Engineering',
            'Mechanical Engineering',
            'Architecture',
            'Textile Engineering',
            'Industrial & Production Engineering',
            'Business Administration (BBA)',
            'Accounting',
            'Finance',
            'Marketing',
            'Management',
            'Economics',
            'English',
            'Bangla',
            'Law',
            'Pharmacy',
            'Public Health',
            'Journalism and Media Studies',
            'International Relations',
            'Political Science',
            'Sociology',
            'Psychology',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Statistics',
            'Biotechnology',
            'Environmental Science',
            'Fashion Design & Technology',
            'Graphic Design',
            'Tourism & Hospitality Management',
            'Islamic Studies',
            'Development Studies',
            'Information Technology',
            'Data Science',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(['name' => $name], ['is_custom' => false]);
        }
    }
}
