<?php

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

if (!function_exists('saveFile')) {
  function saveFile($file, $isImage = false)
  {
    if (!$file) {
      return null;
    }

    $fileName = $file->hashName();
    $file->store('uploads', 'public');

    if ($isImage) {
      $thumbnail = Image::make($file->getRealPath())->fit(250, 250);
      $thumbnail->save(storage_path("app/public/thumbnails/{$fileName}"));
    }

    return $fileName;
  }
}

if (!function_exists('removeFile')) {
  function removeFile($fileName, $isImage = false)
  {
    if ($fileName) {
      $uploadPath = storage_path("app/public/uploads/{$fileName}");
      if (file_exists($uploadPath)) {
        unlink($uploadPath);
      }

      if ($isImage) {
        $thumbnailPath = storage_path("app/public/thumbnails/{$fileName}");
        if (file_exists($thumbnailPath)) {
          unlink($thumbnailPath);
        }
      }
    }
  }
}

if (!function_exists('fetchListData')) {
  function fetchListData(Request $request, $params)
  {
    // Count
    $recordsFiltered = $recordsTotal = with(clone $params->builder)->count();

    // Search
    if ($search = $request->search['value'] ?? '') {
      $params->builder->where(function ($q) use ($request, $search) {
        $q->whereRaw('0');

        foreach ($request->columns as $column) {
          if ($column['searchable'] === 'true') {
            $q->orWhere($column['name'], 'LIKE', '%' . $search . '%');
          }
        }
      });

      $recordsFiltered = with(clone $params->builder)->count();
    }

    // Order
    foreach ($request->order as $order) {
      $column = $request->columns[$order['column']];

      if ($column['orderable'] === 'true') {
        $params->builder->orderBy($column['name'], $order['dir']);
      }
    }

    // Limit
    if ($request->length !== -1) {
      $params->builder->offset($request->start)->limit($request->length);
    }

    $data = $params->builder->get();

    // Add line numbers
    foreach ($data as $key => $value) {
      if ($request->order[0]['dir'] === 'asc') {
        $value->__no__ = intval($request->start) + $key + 1;
      } else {
        $value->__no__ = $recordsFiltered - intval($request->start) - $key;
      }
    }

    // Apply callback
    if (isset($params->rowsCallback)) {
      $data = $data->map($params->rowsCallback);
    }

    return response()->json([
      'draw' => intval($request->draw),
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $data
    ]);
  }
}

if (!function_exists('generateUniqueCode')) {
  function generateUniqueCode($table, $column, $prefix)
  {
    $last = DB::table($table)->latest($column)->first();
    $lastNum = 5000;

    if ($last) {
      [, $lastNum] = explode('-', $last->$column);
      $lastNum = intval($lastNum);
    }

    do {
      $uniqueCode = $prefix . '-' . str_pad(++$lastNum, 6, '0', STR_PAD_LEFT);
    } while (DB::table($table)->where($column, $uniqueCode)->exists());

    return $uniqueCode;
  }
}

if (!function_exists('sendOtpCode')) {
  function sendOtpCode($email)
  {
    OtpCode::where('email', $email)->delete();
    $oc = OtpCode::create([
      'email' => $email,
      'code' => random_int(100000, 999999)
    ]);

    Mail::to($email)->send(new \App\Mail\OtpCodeSent($oc));
  }
}

if (!function_exists('compareOtpCode')) {
  function compareOtpCode($email, $code)
  {
    $oc = OtpCode::firstWhere('email', $email);

    if (!$oc || $oc->created_at->addMinutes(5) < Carbon::now()) {
      return 'The code has expired';
    }

    if ($code !== $oc->code) {
      return 'The code is not valid';
    }

    $oc->delete();

    return true;
  }
}

if (!function_exists('createPasswordResetToken')) {
  function createPasswordResetToken($email)
  {
    DB::table('password_reset_tokens')->where('email', $email)->delete();

    DB::table('password_reset_tokens')->insert([
      'email' => $email,
      'token' => ($token = Str::random(40)),
      'created_at' => Carbon::now()
    ]);

    return $token;
  }
}

if (!function_exists('comparePasswordResetToken')) {
  function comparePasswordResetToken($email, $token, $time = null)
  {
    $row = DB::table('password_reset_tokens')->where('email', $email)->first();

    if (!$row || ($time !== null && Carbon::parse($row->created_at)->addMinutes($time) < Carbon::now())) {
      return 'Le token a expire';
    }

    if ($token !== $row->token) {
      return "Le token n'est pas valide";
    }

    return true;
  }
}

if (!function_exists('deletePasswordResetToken')) {
  function deletePasswordResetToken($email)
  {
    DB::table('password_reset_tokens')->where('email', $email)->delete();
  }
}
