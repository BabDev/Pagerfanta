<?php declare(strict_types=1);

namespace Pagerfanta\Position;

/**
 * Marker interface for an opaque location within a paginated list.
 *
 * Positions allow route generators and views to link to other pages without knowing the pagination strategy in use.
 */
interface Position {}
