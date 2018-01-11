<?php
//match /Shop/123/Product/234
class ShopProduct{
	function post($ctx){
		$ctx -> body = '/Shop/Product->post';
	}
}
