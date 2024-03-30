<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;


class AuthController extends Controller
{
    public function __construct()
    {
            //function constructor ini menjalankan middleware
        $this->middleware(['auth:api', 'verified'], ['except'=>['login','register', 'verify', 'notice', 'resend']]);
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
        ])->sendEmailVerificationNotification(); //verif email
    
        // Lakukan login otomatis dan dapatkan token
       // $token = Auth::login($user);
    
        // Kembalikan response
        return response()->json([
            'status' => true,
            'message' => 'Anda Berhasil Register. Silahkan cek email Anda untuk melakukan verifikasi',
            'user' => $user,
            // 'authorization' => [
            //     'token' => $token,
            //     'type' => 'Bearer'
            // ]
        ]);
    }

    public function login(Request $request){
        //validasi
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //pengecekan gagal dari validator
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors() 
            ], 400);
        }

        //pengecekan berhasil dari validator
         //brebas ini nama variabel
         $loginValue = $request->only('email', 'password');
         //gunakan attemp untuk mencocokkan dengan data menggunakan variabl login value
         //generate token
         $token = Auth::attempt($loginValue);
         //jika tidak sama dengan token 
         if(!$token){
             return response()->json([
                 'status' => false,
                 'message' => 'Email or Password Invalid'
             ], 400);
        }   

        //tampung user aktif
        $user = Auth::user();
        //kirim response json
        return response()->json([
            'status' => true,
            'message' => 'Anda Berhasil Login',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
            ], 200);
        
    }

    public function logout(){
        Auth::logout();

        return response()->json([
            'status' => true,
            'message' => 'Anda Berhasil Logout'
        ], 200);
    }

    //refresh token
    public function refresh(){
        $token = Auth::refresh();
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'message' => 'Anda Berhasil Refresh Token',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
            ], 200);
    }


    //function verify email
    public function verify($id, Request $request){

        //pengecekan
        if(!$request->hasValidSignature()){
            return response()->json([
                'status' => false,
                'message' => 'Verivy Email Fails'
            ], 400);
        }

        //kalau berhasil maka akan ada id pengguna yg dikirim, maka dilakukan pencarian id
        $user = User::find($id);
        // Verifikasi email jika belum terverifikasi
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    
        // Redirect ke halaman home
        return redirect()->to('/');
    }

    //untuk function verification.notice agar user tau kalau mrk blm verify email
    public function notice(){
        return response()->json([
            'status' => false,
            'message' => 'Anda Belum Melakukan Verifikasi Email'
        ], 400);
    }

    //resend email verification
    public function resend(){
        //pengecekan apakah bnr2 sudah verif?
        if(Auth::user()->hasVerifiedEmail()){
            return response()->json([
                'status' => true,
                'message' => 'Email Sudah diverifikasi'
            ], 200);
        }

        //mengambil user aktif 
        Auth::user()->sendEmailVerificationNotification();
        return response()->json([
            'status' => true,
            'message' => 'Link verifikasi telah dikirim ke email anda'
        ], 200);
    }
    
}
