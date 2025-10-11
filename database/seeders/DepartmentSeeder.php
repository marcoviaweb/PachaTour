<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'La Paz',
                'slug' => 'la-paz',
                'capital' => 'La Paz',
                'description' => 'Sede de gobierno de Bolivia, hogar del majestuoso Illimani y la vibrante cultura aymara. La Paz ofrece desde el moderno teleférico hasta los tradicionales mercados de las brujas, pasando por el impresionante Valle de la Luna y las ruinas de Tiwanaku.',
                'short_description' => 'Sede de gobierno con el Illimani, teleféricos y cultura aymara ancestral.',
                'latitude' => -16.5000,
                'longitude' => -68.1193,
                'population' => 2706359,
                'area_km2' => 133985.00,
                'climate' => 'Altiplánico frío y seco',
                'languages' => ['Español', 'Aymara', 'Quechua'],
                'gallery' => [
                    'illimani.jpg',
                    'teleferico-la-paz.jpg',
                    'mercado-brujas.jpg',
                    'valle-luna.jpg',
                    'tiwanaku.jpg'
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Santa Cruz',
                'slug' => 'santa-cruz',
                'capital' => 'Santa Cruz de la Sierra',
                'description' => 'El departamento más extenso y próspero de Bolivia, conocido por su desarrollo económico, biodiversidad amazónica y cultura camba. Desde las misiones jesuíticas hasta los parques nacionales, Santa Cruz combina modernidad con naturaleza exuberante.',
                'short_description' => 'Departamento próspero con biodiversidad amazónica y cultura camba vibrante.',
                'latitude' => -17.7833,
                'longitude' => -63.1821,
                'population' => 2655084,
                'area_km2' => 370621.00,
                'climate' => 'Tropical cálido y húmedo',
                'languages' => ['Español', 'Guaraní'],
                'gallery' => [
                    'santa-cruz-centro.jpg',
                    'misiones-jesuiticas.jpg',
                    'parque-amboro.jpg',
                    'samaipata.jpg'
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Cochabamba',
                'slug' => 'cochabamba',
                'capital' => 'Cochabamba',
                'description' => 'Conocido como la "Ciudad de la Eterna Primavera" por su clima privilegiado. Cochabamba es el corazón gastronómico de Bolivia, hogar del Cristo de la Concordia y punto de encuentro de diversas culturas andinas.',
                'short_description' => 'Ciudad de la Eterna Primavera, corazón gastronómico de Bolivia.',
                'latitude' => -17.3895,
                'longitude' => -66.1568,
                'population' => 1758143,
                'area_km2' => 55631.00,
                'climate' => 'Templado de valle',
                'languages' => ['Español', 'Quechua'],
                'gallery' => [
                    'cristo-concordia.jpg',
                    'valle-cochabamba.jpg',
                    'mercado-cancha.jpg',
                    'tunari.jpg'
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Potosí',
                'slug' => 'potosi',
                'capital' => 'Potosí',
                'description' => 'La "Villa Imperial" que una vez fue la ciudad más rica del mundo gracias a sus minas de plata. Potosí conserva su arquitectura colonial y alberga el famoso Cerro Rico, además del impresionante Salar de Uyuni, la mayor planicie de sal del mundo.',
                'short_description' => 'Villa Imperial con el Cerro Rico y el espectacular Salar de Uyuni.',
                'latitude' => -19.5723,
                'longitude' => -65.7550,
                'population' => 823517,
                'area_km2' => 118218.00,
                'climate' => 'Altiplánico frío y árido',
                'languages' => ['Español', 'Quechua'],
                'gallery' => [
                    'salar-uyuni.jpg',
                    'cerro-rico.jpg',
                    'potosi-colonial.jpg',
                    'flamingos-uyuni.jpg',
                    'tren-cementerio.jpg'
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Oruro',
                'slug' => 'oruro',
                'capital' => 'Oruro',
                'description' => 'Capital del folklore boliviano, famosa por su Carnaval declarado Patrimonio de la Humanidad por la UNESCO. Oruro combina tradición minera con expresiones culturales únicas, siendo el epicentro de la devoción a la Virgen del Socavón.',
                'short_description' => 'Capital del folklore con el Carnaval Patrimonio de la Humanidad.',
                'latitude' => -17.9667,
                'longitude' => -67.1167,
                'population' => 494178,
                'area_km2' => 53588.00,
                'climate' => 'Altiplánico seco y frío',
                'languages' => ['Español', 'Quechua', 'Aymara'],
                'gallery' => [
                    'carnaval-oruro.jpg',
                    'virgen-socavon.jpg',
                    'diablada.jpg',
                    'santuario-socavon.jpg'
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Chuquisaca',
                'slug' => 'chuquisaca',
                'capital' => 'Sucre',
                'description' => 'Capital constitucional de Bolivia, conocida como la "Ciudad Blanca" por su arquitectura colonial perfectamente conservada. Sucre es Patrimonio de la Humanidad y cuna de la independencia americana, con importantes sitios paleontológicos.',
                'short_description' => 'Capital constitucional, Ciudad Blanca patrimonio de la humanidad.',
                'latitude' => -19.0333,
                'longitude' => -65.2627,
                'population' => 576153,
                'area_km2' => 51524.00,
                'climate' => 'Templado de valle',
                'languages' => ['Español', 'Quechua'],
                'gallery' => [
                    'sucre-colonial.jpg',
                    'casa-libertad.jpg',
                    'convento-recoleta.jpg',
                    'cal-orck.jpg'
                ],
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Tarija',
                'slug' => 'tarija',
                'capital' => 'Tarija',
                'description' => 'El "Valle de la Eterna Primavera" boliviano, famoso por sus vinos y singanis de calidad mundial. Tarija ofrece paisajes de viñedos, tradiciones gaucho-andaluzas y una gastronomía única que combina influencias argentinas y bolivianas.',
                'short_description' => 'Valle vinícola con los mejores vinos y singanis de Bolivia.',
                'latitude' => -21.5355,
                'longitude' => -64.7296,
                'population' => 482196,
                'area_km2' => 37623.00,
                'climate' => 'Templado subtropical',
                'languages' => ['Español'],
                'gallery' => [
                    'vinedos-tarija.jpg',
                    'singani-tarija.jpg',
                    'valle-concepcion.jpg',
                    'tarija-colonial.jpg'
                ],
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Beni',
                'slug' => 'beni',
                'capital' => 'Trinidad',
                'description' => 'Corazón de la Amazonía boliviana, hogar de una biodiversidad extraordinaria y culturas indígenas ancestrales. Beni ofrece experiencias únicas de ecoturismo, desde la observación de delfines rosados hasta la exploración de lomas precolombinas.',
                'short_description' => 'Amazonía boliviana con biodiversidad extraordinaria y culturas ancestrales.',
                'latitude' => -14.8333,
                'longitude' => -64.9000,
                'population' => 421196,
                'area_km2' => 213564.00,
                'climate' => 'Tropical húmedo',
                'languages' => ['Español', 'Moxeño', 'Baure', 'Canichana'],
                'gallery' => [
                    'delfines-beni.jpg',
                    'lomas-casarabe.jpg',
                    'trinidad-beni.jpg',
                    'rio-mamore.jpg'
                ],
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Pando',
                'slug' => 'pando',
                'capital' => 'Cobija',
                'description' => 'La puerta de entrada a la Amazonía boliviana, frontera con Brasil y Perú. Pando conserva vastas extensiones de selva virgen, es el hogar de comunidades indígenas y ofrece aventuras de turismo ecológico en uno de los ecosistemas más diversos del planeta.',
                'short_description' => 'Puerta amazónica con selva virgen y comunidades indígenas.',
                'latitude' => -11.0267,
                'longitude' => -68.7692,
                'population' => 110436,
                'area_km2' => 63827.00,
                'climate' => 'Tropical lluvioso',
                'languages' => ['Español', 'Machinerí', 'Yaminawa'],
                'gallery' => [
                    'selva-pando.jpg',
                    'cobija-frontera.jpg',
                    'rio-acre.jpg',
                    'comunidades-indigenas.jpg'
                ],
                'is_active' => true,
                'sort_order' => 9,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }

        $this->command->info('✅ Creados 9 departamentos de Bolivia con datos reales');
    }
}
