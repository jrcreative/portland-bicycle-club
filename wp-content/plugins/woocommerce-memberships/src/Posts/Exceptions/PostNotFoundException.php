<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2026, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Posts\Exceptions;

use RuntimeException;
use SkyVerge\WooCommerce\Memberships\Contracts\ConvertToWpErrorContract;
use Throwable;
use WP_Error;

defined('ABSPATH') or exit;

/**
 * Thrown when a post lookup fails — either because no post exists with the given ID
 * or because the value passed in is not a positive integer.
 *
 * @since 1.29.0
 */
class PostNotFoundException extends RuntimeException implements ConvertToWpErrorContract
{
	public function __construct($message = "", $code = 404, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Builds an exception for a specific post ID that could not be resolved.
	 *
	 * @since 1.29.0
	 *
	 * @param int $postId
	 * @return self
	 */
	public static function forPostId(int $postId): self
	{
		return new self(
			sprintf(__('Post not found (ID: %d).', 'woocommerce-memberships'), $postId)
		);
	}


	/** @inheritDoc */
	public function toWpError(): WP_Error
	{
		return new WP_Error('post_not_found', $this->getMessage(), ['status' => $this->getCode()]);
	}
}
