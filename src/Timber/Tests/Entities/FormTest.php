<?php
namespace Timber\Tests\Entities;

use Timber\Entity;
use Timber\Forms\Form as AbstractForm;
use Timber\Forms\Element\Hidden;
use Phalcon\Validation\Validator\Identical;
use DOMXpath;
use Phalcon\DI\FactoryDefault;
use Timber\Forms\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    public $di = null;

    public function setup()
    {
       $this->di = new FactoryDefault();
       $this->di['session'] = function() {
           //All variables created will prefixed with mwm-
           $session = new \Phalcon\Session\Adapter\Files(
               ['uniqueId' => 'mwm-']
           );
           $session->start();
           return $session;
       };
    }

    /**
     *
     * @todo check for expected exception
     * @disable this doesn't seem to work because the session doesn't work on the cli
     */
    public function testCSRF()
    {
        
        $session = $this->di->get('session');

        $session->start();
        $this->assertTrue($session->isStarted(), 'sessionnot started');
        $this->assertTrue($session->status() == $session::SESSION_ACTIVE, "session hasn't been started");

        $this->assertNotNull($session);

        $security = $this->di->get('security');
        $this->assertNotNull($security);

        $token = $security->getToken();


        $sessionToken = $security->getSessionToken();


        $this->assertNotNull($token, "Token is null, do you have a working session ?");
        $this->assertNotNull($sessionToken, "Session Token is null, do you have a working session ?");
        $this->assertEquals($token, $sessionToken, "Token and sessionToken don't match");

        $form = new MyForm(null, ['foo'=>'bar']);
        //$form->setSecurity($security);
        $security->destroyToken();

        //echo get_class($form);
        //$form->initialize();
        //echo //$form->__saveXML();


    }
}

class MyForm extends AbstractForm
{

    public function initialize($entity = null, $options = null)
    {

        // Add CSRF
        // @todo this is annoying need to make this whole process better
        // does calling getToken on the subsequent instanciation
        // mess with the session, I think so...grrr
        $csrf = new Hidden('csrf');
        //$sessionToken = $this->security->getSessionToken();
        $csrf->addValidator(new Identical([
                'label'    => $csrf->getLabel()
        ]));
        //$this->add($csrf);
    }
}
