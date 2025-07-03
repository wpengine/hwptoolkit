<?php

namespace WPGraphQL\Velocity\Data;

interface DataSourceInterface
{
	public function get_id() : string;

	public function get_type() : string;

	public function get_data() : array;
}
