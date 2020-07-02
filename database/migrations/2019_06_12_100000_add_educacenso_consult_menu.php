<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEducacensoConsultMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $submenu = Menu::create([
            'parent_id' => Menu::query()->where('process', 70)->firstOrFail()->getKey(),
            'title' => 'Consultas',
            'order' => 3,
            'type' => 2,
        ]);


        Menu::create([
            'parent_id' => $submenu->getKey(),
            'title' => 'Consulta 1ª fase - Matrícula inicial',
            'description' => 'Consulta dos dados que serão exportados para o Educacenso',
            'link' => '/educacenso/consulta',
            'order' => 1,
            'type' => 3,
            'process' => 847,
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()
            ->where('process', 847)
            ->delete();

        Menu::query()
            ->where('parent_id', Menu::query()->where('process', 70)->firstOrFail()->getKey())
            ->where('title', 'Consultas')
            ->delete();
    }
}