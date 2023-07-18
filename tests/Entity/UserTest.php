<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use \Symfony\Component\Validator\Constraints\Email;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetId(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testSetPassword(): void
    {
        $password = 'mypassword';
        $this->user->setPassword($password);

        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testSetEmail(): void
    {
        $email = 'user@example.com';
        $this->user->setEmail($email);

        // Assert that the email is in a valid format
        $validator = Validation::createValidator();
        $errors = $validator->validate($email, new Email());
        
        $this->assertEquals(0, count($errors), (string) $errors);

        $this->assertEquals($email, $this->user->getEmail());
    }

    public function testSetAddressZipcode(): void
    {
        $zipcode = '33300';
        $this->user->setAddressZipcode($zipcode);

        // Assert that the zipcode is a number
        $this->assertTrue(is_numeric($zipcode));

        $this->assertEquals($zipcode, $this->user->getAddressZipcode());
    }

    public function testSetAddressCountry(): void
    {
        $country = 'France';
        $this->user->setAddressCountry($country);
        $this->assertEquals($country, $this->user->getAddressCountry());
    }

    public function testSetFirstName(): void
    {
        $firstName = 'John';
        $this->user->setFirstName($firstName);
        $this->assertEquals($firstName, $this->user->getFirstName());
    }

    public function testSetLastName(): void
    {
        $lastName = 'Doe';
        $this->user->setLastName($lastName);
        $this->assertEquals($lastName, $this->user->getLastName());
    }

    public function testToString(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $this->user->setFirstName($firstName);
        $this->user->setLastName($lastName);
        $this->assertEquals("{$firstName} {$lastName}", $this->user->__toString());
    }
}
