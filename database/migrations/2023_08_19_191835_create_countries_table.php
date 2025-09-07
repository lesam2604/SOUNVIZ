<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('iso3', 3)->nullable();
            $table->char('iso2', 2)->nullable();
            $table->char('numeric_code', 3)->nullable();
            $table->string('phone_code')->nullable();
            $table->string('capital')->nullable();
            $table->string('currency')->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('tld')->nullable();
            $table->string('native')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('nationality')->nullable();
            $table->json('timezones')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('emoji')->nullable();
            $table->string('emojiU')->nullable();
            $table->timestamps();
        });

        $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }

    private function fill()
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load(storage_path('app/data/countries.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->toArray() as $row) {
            if ($row[0] !== 'id') {
                Country::create([
                    'id' => $row[0],
                    'name' => $row[1],
                    'iso3' => $row[2],
                    'iso2' => $row[3],
                    'numeric_code' => $row[4],
                    'phone_code' => $row[5],
                    'capital' => $row[6],
                    'currency' => $row[7],
                    'currency_name' => $row[8],
                    'currency_symbol' => $row[9],
                    'tld' => $row[10],
                    'native' => $row[11],
                    'region' => $row[12],
                    'subregion' => $row[13],
                    'nationality' => $row[14],
                    'timezones' => $row[15],
                    'latitude' => $row[16],
                    'longitude' => $row[17],
                    'emoji' => $row[18],
                    'emojiU' => $row[19]
                ]);
            }
        }
    }
};
