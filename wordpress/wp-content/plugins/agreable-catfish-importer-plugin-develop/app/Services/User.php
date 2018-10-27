<?php

namespace AgreableCatfishImporterPlugin\Services;

/**
 * Class User
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
/**
 * Class User
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
abstract class User {

	/**
	 * @param $email
	 *
	 * @return bool|int
	 */
	public static function checkUserByEmail( $email ) {
		if ( ( $user = get_user_by( 'email', $email ) ) ) {
			return $user->ID;
		}

		return null;
	}

	/**
	 * @param $login
	 *
	 * @return int|null
	 */
	public static function checkUserByLogin( $login ) {
		if ( ( $user = get_user_by( 'login', $login ) ) ) {
			return $user->ID;
		}

		return null;
	}

	/**
	 * @param $object
	 *
	 * @return int|\WP_Error
	 */
	public static function insertCatfishUser( $object ) {
		$user_data = array(
			'user_login'    => $object->slug,
			'user_nicename' => $object->slug,
			'user_email'    => $object->emailAddress,
			'display_name'  => $object->name,
			'description'   => $object->biography,
			'role'          => 'purgatory',
			'user_pass'     => null
		);
		$user_id   = wp_insert_user( $user_data );

		return $user_id;
	}

	/**
	 * @return int|null
	 */
	public static function getDefaultUser() {

		$user = get_user_by( 'login', 'shortlistteam' );

		if ( $user ) {
			return $user->ID;
		}

		return null;
	}

	/**
	 * @param \stdClass $author
	 */
	public static function findUserFromClockObject( $author ) {

		$userId = null;

		if ( isset( $author->emailAddress ) && $author->emailAddress ) {
			$userId = self::checkUserByEmail( $author->emailAddress );
		}

		if ( ! $userId && isset( $author->slug ) && $author->slug ) {
			$userId = self::checkUserByLogin( $author->slug );
		}

		if ( ! $userId && isset( $author->slug, $author->name ) && $author->name && $author->slug ) {
			$userId = self::insertCatfishUser( $author );
		}

		if ( ! $userId ) {
			$userId = self::getDefaultUser();
		}

		return $userId;

	}
}
