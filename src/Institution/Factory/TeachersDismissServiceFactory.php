<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.01.17
 * Time: 17:00
 */

namespace Institution\Factory;


use Institution\Service\TeachersDismissService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TeachersDismissServiceFactory implements FactoryInterface
{
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        return new TeachersDismissService(
            $serviceLocator->get( 'Institution\Mapper\TeachersMapper' ),
            $serviceLocator->get( 'Users\Mapper\UsersMapper' ),
            $serviceLocator->get( 'Curriculum\Mapper\CurriculumMapper' ),
            $serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' )
        );
    }
}