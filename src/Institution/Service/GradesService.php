<?php

namespace Institution\Service;

use Institution\Mapper\GradesMapperInterface;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Exception\UnauthorizedException;
use Institution\Model\Group;
use Institution\Model\Grade;
use Authorization\Identity\Identity;
use Application\Exception\RuntimeException;
use Application\Service\Exception\ServiceException;

class GradesService implements GradesServiceInterface
{

	/**
	 *
	 * @var Identity
	 */
	protected $identity;

	/**
	 *
	 * @var \Institution\Mapper\GradesMapperInterface
	 */
	protected $mapper;

	/**
	 *
	 * @var AuthorizationServiceInterface
	 */
	protected $authorizationService;

	public function __construct( Identity $identity, GradesMapperInterface $mapper, AuthorizationServiceInterface $authorizationService )
	{
		$this->identity = $identity;
		$this->mapper = $mapper;
		$this->authorizationService = $authorizationService;
	}

	/**
	 *
	 * @param Group|int $group
	 * @return Grade[]
	 */
	public function fetch( $group )
	{
		if ( $group instanceof Group ) {
			$group = $group->id;
		}

		return $this->mapper->fetch( $group );
	}

	/**
	 *
	 * @param int $groups_id
	 * @param string $begin_year
	 * @param boolean $secured Whether to check permission with assertion
	 * @param string $permission Permission code
	 * @return Grade
	 * @throws UnauthorizedException
	 */
	public function get( $groups_id, $begin_year, $secured = true, $permission = 'groups.grades.management', Group $group = null )
	{
		$grade = $this->mapper->get( $groups_id, $begin_year );

		if ( $secured ) {
			if ( $this->authorizationService->isGranted( $permission, $group, $grade ) ) {
				return $grade;
			} else {
				throw new UnauthorizedException();
			}
		}

		return $grade;
	}

	/**
	 *
	 * @param Grade $grade
	 * @param Group $group
	 * @return Grade
	 * @throws UnauthorizedException
	 * @throws ServiceException
	 */
	public function save( Grade $grade, Group $group )
	{
		$grade->setIsNewRecord( $this->get( $grade->groups_id, $grade->begin_year, true, 'groups.grades.management', $group ) ? false : true );
		
		if ( $grade->getIsNewRecord() && $this->authorizationService->isGranted( 'groups.management' ) ) {
			$grade = $this->mapper->create( $grade );
		} elseif ( !$grade->getIsNewRecord() && $this->authorizationService->isGranted( 'groups.grades.management', $group, $grade ) ) {
			$grade = $this->mapper->update( $grade );
		} else {
			throw new UnauthorizedException();
		}

		if ( $grade ) {
			return $grade;
		} else {
			throw new ServiceException( 'Error occured while trying to save a grade!' );
		}
	}

	/**
	 *
	 * @param Grade $grade
	 * @param Group $group
	 * @return boolean
	 * @throws RuntimeException
	 * @throws UnauthorizedException
	 */
	public function delete( Grade $grade, Group $group = null )
	{
		if ( $this->authorizationService->isGranted( 'groups.grades.management', $group, $grade ) ) {
			return $this->mapper->delete( $grade );
		} else {
			throw new UnauthorizedException();
		}
	}

	protected function beginTransaction()
	{
		if ( is_callable( [$this->mapper, 'beginTransaction' ] ) ) {
			$this->mapper->beginTransaction();
		}
	}

	protected function commit()
	{
		if ( is_callable( [$this->mapper, 'commit' ] ) ) {
			$this->mapper->commit();
		}
	}

	protected function rollback()
	{
		if ( is_callable( [$this->mapper, 'rollback' ] ) ) {
			$this->mapper->rollback();
		}
	}

}
