<?php

use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

/**
 * @api {post} /user/signup Sign up a new user.
 *
 * @apiName Sign up
 *
 * @apiGroup User
 *
 * @apiDescription This path sign up a user on the system.
 *
 * @apiPermission Any
 *
 * @apiParam {String} name  The name of the new user.
 * @apiParam {String} email  The email of the new user.
 * @apiParam {String} password  The password of the new user.
 * @apiParam {String} apyKey  Key to sign up  a user if the user system is disabled.
 *
 * @apiUse INVALID_NAME
 * @apiUse INVALID_EMAIL
 * @apiUse INVALID_PASSWORD
 * @apiUse INVALID_CAPTCHA
 * @apiUse USER_SYSTEM_DISABLED
 * @apiUse USER_EXISTS
 * @apiUse ALREADY_BANNED
 * @apiUse NO_PERMISSION
 *
 * @apiSuccess {Object} data
 *
 */

class SignUpController extends Controller {
    const PATH = '/signup';
    const METHOD = 'POST';

    private $userEmail;
    private $userName;
    private $userPassword;
    private $verificationToken;
    private $csvImported;
    
    public function __construct($csvImported = false) {
        $this->csvImported = $csvImported;
    }

    public function validations() {
        $validations = [
            'permission' => 'any',
            'requestData' => [
                'name' => [
                    'validation' => DataValidator::length(2, 55),
                    'error' => ERRORS::INVALID_NAME
                ],
                'email' => [
                    'validation' => DataValidator::email(),
                    'error' => ERRORS::INVALID_EMAIL
                ],
                'password' => [
                    'validation' => DataValidator::length(5, 200),
                    'error' => ERRORS::INVALID_PASSWORD
                ]
            ]
        ];
        
        if(!$this->csvImported) {
            $validations['requestData']['captcha'] = [
                'validation' => DataValidator::captcha(),
                'error' => ERRORS::INVALID_CAPTCHA
            ];
        }
        
        return $validations;
    }

    public function handler() {
        if(!Controller::isUserSystemEnabled()) {
            throw new Exception(ERRORS::USER_SYSTEM_DISABLED);
        }
        
        $this->storeRequestData();
        $apiKey = APIKey::getDataStore(Controller::request('apiKey'), 'token');

        $existentUser = User::getUser($this->userEmail, 'email');

        if (!$existentUser->isNull()) {
            throw new Exception(ERRORS::USER_EXISTS);
        }
        $banRow = Ban::getDataStore($this->userEmail,'email');

        if (!$banRow->isNull()) {
            throw new Exception(ERRORS::ALREADY_BANNED);
        }

        if (!Setting::getSetting('registration')->value && $apiKey->isNull() && !$this->csvImported) {
            throw new Exception(ERRORS::NO_PERMISSION);
        }

        $userId = $this->createNewUserAndRetrieveId();
        $this->sendRegistrationMail();

        Response::respondSuccess([
            'userId' => $userId,
            'userEmail' => $this->userEmail
        ]);
        
        Log::createLog('SIGNUP', null, User::getDataStore($userId));
    }
    
    public function storeRequestData() {
        $this->userName = Controller::request('name');
        $this->userEmail = Controller::request('email');
        $this->userPassword = Controller::request('password');
        $this->verificationToken = Hashing::generateRandomToken();
    }

    public function createNewUserAndRetrieveId() {
        $userInstance = new User();

        $userInstance->setProperties([
            'name' => $this->userName,
            'signupDate' => Date::getCurrentDate(),
            'tickets' => 0,
            'email' => $this->userEmail,
            'password' => Hashing::hashPassword($this->userPassword),
            'verificationToken' => $this->verificationToken
        ]);

        return $userInstance->store();
    }
    
    public function sendRegistrationMail() {
        $mailSender = new MailSender();
        
        $mailSender->setTemplate(MailTemplate::USER_SIGNUP, [
            'to' => $this->userEmail,
            'name' => $this->userName,
            'url' => Setting::getSetting('url')->getValue(),
            'verificationToken' => $this->verificationToken
        ]);
        
        $mailSender->send();
    }
}
