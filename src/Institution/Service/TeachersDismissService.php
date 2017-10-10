<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.01.17
 * Time: 16:53
 */

namespace Institution\Service;


use Application\Service\Exception\ServiceException;
use Curriculum\Mapper\CurriculumMapperInterface;
use Institution\Mapper\TeachersMapperInterface;
use Institution\Model\Teacher;
use Users\Mapper\UsersMapperInterface;
use ZfcRbac\Exception\UnauthorizedException;
use ZfcRbac\Service\AuthorizationServiceInterface;

class TeachersDismissService implements TeachersDismissServiceInterface
{
    /**
     *
     * @var \Institution\Mapper\TeachersMapperInterface
     */
    protected $mapper;

    /**
     * @var UsersMapperInterface
     */
    protected $umapper;

    /**
     * @var CurriculumMapperInterface
     */
    protected $cmapper;

    /**
     *
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    public function __construct(
        TeachersMapperInterface $mapper,
        UsersMapperInterface $umapper,
        CurriculumMapperInterface $cmapper,
        AuthorizationServiceInterface $authorizationService
    )
    {
        $this->mapper = $mapper;
        $this->umapper = $umapper;
        $this->cmapper = $cmapper;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param Teacher $teacher
     * @param bool    $addVacancy
     *
     * @return false|Teacher if $addVacancy is true then newly created vacancy will be returned, otherwise $teacher
     *                       parameter will be returned
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    public function dismiss(Teacher $teacher, bool $addVacancy = false)
    {
        if ($this->authorizationService->isGranted('teachers.dismiss', $teacher)) {
            $teacher->dismissed = 1;

            $this->beginTransaction();

            if (!$result = $this->mapper->update($teacher)) {
                throw new ServiceException('Error while trying to dismiss a teacher!');
            }

            if( $teacher->users_id ){
                $user = $this->umapper->get($teacher->users_id);
                $user->active = 0;
                if(!$this->umapper->save($user)){
                    throw new ServiceException('Error while trying to deactivate teacher\'s user!');
                }
            }

            if ($addVacancy) {
                $firstname = strlen($teacher->firstname) ? mb_substr($teacher->firstname, 0, 1, 'utf-8') . '.' : '';
                $middlename = strlen($teacher->middlename) ? mb_substr($teacher->middlename, 0, 1, 'utf-8') . '.' : '';

                $vacancy = new Teacher(
                    [
                        'accounts_id' => $teacher->accounts_id,
                        'users_id' => null,
                        'firstname' => $firstname,
                        'lastname' => $teacher->lastname,
                        'middlename' => $middlename,
                        'email' => null,
                        'phone' => null,
                        'vacancy' => 1
                    ]
                );

                $vacancy->subjects_id = $teacher->subjects_id;

                if (!$result = $this->mapper->create($vacancy)) {
                    $this->rollback();
                    throw new ServiceException('Error occurred while trying to create a vacancy!');
                }

                if (!$this->cmapper->replaceTeacher($teacher->id, $result->id)) {
                    $this->rollback();
                    throw new ServiceException('Error occurred while trying to replace a teacher by vacancy!');
                }
            }

            $this->commit();
        } else {
            throw new UnauthorizedException();
        }

        return $result;
    }

    protected function beginTransaction()
    {
        if (is_callable([$this->mapper, 'beginTransaction'])) {
            $this->mapper->beginTransaction();
        }
    }

    protected function commit()
    {
        if (is_callable([$this->mapper, 'commit'])) {
            $this->mapper->commit();
        }
    }

    protected function rollback()
    {
        if (is_callable([$this->mapper, 'rollback'])) {
            $this->mapper->rollback();
        }
    }
}