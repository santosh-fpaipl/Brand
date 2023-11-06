<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $email;
    private $password;

    public function setUp(): void 
    {
        parent::setUp();
        $this->email = $this->faker->unique()->safeEmail;
        $this->password = 'password';
    }
    
    public function testRegister(): void
    {
        $response = $this->createUser();
        $response->assertStatus(302); // Check that the registration succeeded with a redirection
    }

    public function testLogin(){
        $this->createUser();
        $response = $this->login();
        $response->assertStatus(302); // Check that the login succeeded with a redirection
        $this->assertEquals(auth()->user()->email, $this->email);
        $this->assertAuthenticated();
    }

    public function testForgotPassword(){
        $this->createUser();
        $response = $this->post('/api/forgot-password', [
            'email' => $this->email,
        ]);
        $response->assertStatus(302); //// Check that the forgot password succeeded with a redirection
    }

    public function testGetLoginUserDetail(){
        $this->createUser();
        $this->login();
        $response = $this->get('/api/user');
        $response->assertOk();
    }

    public function testLogout(){
        /**
         * The HTTP status code 204 corresponds to the "No Content" response, 
         * and it is commonly used for successful API requests 
         * that do not return any content in the response body. 
         */
        $this->createUser();
        $this->login();
        $response = $this->post('/api/logout');
        $response->assertStatus(204);
        $this->refreshApplication();
        $this->assertGuest();
    }

    public function testUpdatePassword(){
        $this->createUser();
        $this->login();

        $this->post('/api/email/verify', [
            'otp' => auth()->user()->email_verification_otp,
        ]);
        
        $response = $this->put('/api/user/password', [
            'current_password' => $this->password,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::find(auth()->user()->id);
        $response->assertStatus(302); // Check that the password change was successful with a redirection
        //dump($user->password); // Debugging statement
        //dump(password_hash('password123', PASSWORD_DEFAULT)); 
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function testEmailVerify(){
        $this->createUser();
        $this->login();
        $response = $this->post('/api/email/verify', [
            'otp' => auth()->user()->email_verification_otp,
        ]);
        $response->assertStatus(302); // Expecting a redirection
        $this->assertNotNull(auth()->user()->email_verified_at);
    }

    public function testUpdateProfile(){
        $this->createUser();
        $this->login();

        $this->post('/api/email/verify', [
            'otp' => auth()->user()->email_verification_otp,
        ]);

        $newName = 'Santosh 2222';

        $response = $this->put('/api/user/profile-information', [
            'email' => auth()->user()->email,
            'name' => $newName,
            'mobile' => '8527117535',
            'company' => auth()->user()->company,
            'gst_no' => auth()->user()->gst_no,
            'pan_no' => auth()->user()->pan_no
        ]);

        $response->assertStatus(302); // Check that the profile change was successful with a redirection
        
        $user = User::find(auth()->user()->id);

        $this->assertEquals($newName, $user->name);
    
    }

    public function testResendVerify(){
        $this->createUser();
        $this->login();
        $response = $this->post('/api/email/verification-resend');
        $response->assertStatus(302); // Check that the resend verify was successful with a redirection
    }


    public function createUser(){
        $user = $this->post('/api/register', [
            'name' => $this->faker->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);
        return $user;
    }

    public function login(){
        $login = $this->post('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);
        return $login;
    }
}
