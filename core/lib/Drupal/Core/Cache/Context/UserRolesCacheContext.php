<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\UserRolesCacheContext.
 */

namespace Drupal\Core\Cache\Context;

/**
 * Defines the UserRolesCacheContext service, for "per role" caching.
 *
 * Only use this cache context when checking explicitly for certain roles. Use
 * user.permissions for anything that checks permissions.
 */
class UserRolesCacheContext extends UserCacheContext implements CalculatedCacheContextInterface{

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("User's roles");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($role = NULL) {
    // User 1 does not actually have any special behavior for roles; this is
    // added as additional security and backwards compatibility protection for
    // SA-CORE-2015-002.
    // @todo Remove in Drupal 9.0.0.
    if ($this->user->id() == 1) {
      return 'is-super-user';
    }
    if ($role === NULL) {
      return 'r.' . implode(',', $this->user->getRoles());
    }
    else {
      return 'r.' . $role . '.' . (in_array($role, $this->user->getRoles()) ? '0' : '1');
    }
  }

}
