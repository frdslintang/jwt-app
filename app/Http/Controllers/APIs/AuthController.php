<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;


class AuthController extends Controller
{
    //function constructor
    public function __construct()
    {
        $this->middleware('auth:api', ['except'=>['login','register']]);
    }
    
    public function register(Request $request){
        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email'=> 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ], [
            // Pesan yang ditampilkan
            'name.required' => 'Nama Tidak Boleh Kosong',
            'email.required' => 'Email Harus Diisi',
            'email.email'=>'Format Email Tidak Valid',
            'email.unique' => 'Email Sudah Terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Panjang Karakter Password Min 8',
            'password.confirmed' => 'Password Tidak Sama'
        ]);
    
        // Pengecekan validasi
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422); // Ubah kode status menjadi 422
        }
    
        // Simpan user ke database
        $user = User::create([
            'name' => $request->name,
            'email' =>$request->email,
            'password' => Hash::make($request->password), // Enkripsi password sebelum disimpan
        ]);
    
        // Lakukan login otomatis dan dapatkan token
        $token = Auth::login($user);
    
        // Kembalikan response
        return response()->json([
            'status' => true,
            'message' => 'Anda Berhasil Register',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ]);
    }

    }
