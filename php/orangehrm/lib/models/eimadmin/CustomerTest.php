<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */


// Call CustomerTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "CustomerTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once "testConf.php";
require_once 'Customer.php';
require_once ROOT_PATH . '/lib/common/UniqueIDGenerator.php';

/**
 * Test class for Customer.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-22 at 15:38:57.
 */
class CustomerTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
	public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("CustomerTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
	protected function setUp() {

    	$this->classCustomer = new Customer();

    	$conf = new Conf();

    	$this->connection = mysql_connect($conf->dbhost.":".$conf->dbport, $conf->dbuser, $conf->dbpass);

        mysql_select_db($conf->dbname);

		mysql_query("TRUNCATE TABLE `ohrm_project`");
		mysql_query("TRUNCATE TABLE `ohrm_customer`");

        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1001','zanfer1','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1002','zanfer2','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1003','zanfer3','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1004','zanfer4','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1005','zanfer5','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1006','zanfer6','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1007','zanfer7','forrw',0 )");
		UniqueIDGenerator::getInstance()->initTable();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
	protected function tearDown() {
		mysql_query("TRUNCATE TABLE `ohrm_project`");
		mysql_query("TRUNCATE TABLE `ohrm_customer`", $this->connection);
		UniqueIDGenerator::getInstance()->initTable();
    }

    public function testAddCustomer() {

    	$this->classCustomer->setCustomerName("Dodle");
    	$this->classCustomer->setCustomerDescription("jhgjhg");

    	$res = $this->classCustomer->addCustomer();
    	$id = $this->classCustomer->getCustomerId();
    	$res = $this->classCustomer->fetchCustomer($id);

	    $this->assertNotNull($res, "No record found");

	   	$this->assertEquals($res->getCustomerId(),$id,'Id Not Found');
	   	$this->assertEquals($res->getCustomerName(),'Dodle','Name Not Found');
	   	$this->assertEquals($res->getCustomerDescription(),'jhgjhg','Description Not Found');
    }

    public function testUpdateCustomer() {

		$res  = $this->classCustomer->fetchCustomer("1007");
		$this->assertNotNull($res, "No record found");
    	$res->setCustomerName("BoooBU");
    	$res->updateCustomer();
    	$res  = $this->classCustomer->fetchCustomer("1007");
    	$this->assertNotNull($res, "No record found");

	   	$this->assertEquals($res->getCustomerId(),'1007','Id Not Found');
	   	$this->assertEquals($res->getCustomerName(),'BoooBU','Name Not Found');
	  	$this->assertEquals($res->getCustomerDescription(),'forrw','Description Not Found');
    }

    /**
     * @todo Implement testDeleteCustomer().
     */
    public function testDeleteCustomer() {

		// Delete customer without any project
       	$res  = $this->classCustomer->fetchCustomer("1007");
        $this->assertNotNull($res, "record Not found");

    	$res->deleteCustomer();
    	$res  = $this->classCustomer->fetchCustomer("1007");
    	$this->assertNull($res, "record found");

    	// Add 2 projects to customer 1001
    	$this->assertTrue(mysql_query("INSERT INTO ohrm_project(project_id, id, name, description, is_deleted)" .
    			" VALUES(1, 1001, 'Test Project1', 'description', 0)"), mysql_error());
    	$this->assertTrue(mysql_query("INSERT INTO ohrm_project(project_id, id, name, description, is_deleted)" .
    			" VALUES(2, 1001, 'Test Project2', 'description', 0)"), mysql_error());

		// Add 2 projects to customer 1002
    	$this->assertTrue(mysql_query("INSERT INTO ohrm_project(project_id, id, name, description, is_deleted)" .
    			" VALUES(3, 1002, 'Test Project3', 'description', 0)"), mysql_error());
    	$this->assertTrue(mysql_query("INSERT INTO ohrm_project(project_id, id, name, description, is_deleted)" .
    			" VALUES(4, 1002, 'Test Project4', 'description', 0)"), mysql_error());

		// Delete customer and verify that customer was deleted.
       	$res  = $this->classCustomer->fetchCustomer("1001");
        $this->assertNotNull($res, "record Not found");

    	$res->deleteCustomer();
    	$res  = $this->classCustomer->fetchCustomer("1001");
    	$this->assertNull($res, "record found");

    	// Verify that projects belonging to customer was deleted.
		$count = $this->_getCount("SELECT COUNT(*) FROM ohrm_project WHERE id=1001 AND is_deleted=0");
		$this->assertEquals(0, $count, "Projects not deleted when customer deleted.");

		// Verify that deleted was set to 1
		$count = $this->_getCount("SELECT COUNT(*) FROM ohrm_project WHERE id=1001 AND is_deleted=1");
		$this->assertEquals(2, $count, "deleted value not correct for projects belonging to deleted customer.");

		// Verify that customer 1002 was not deleted.
       	$this->assertNotNull($this->classCustomer->fetchCustomer("1002"), "Customer 1002 was deleted as well!");

		// Verify that projects belonging to customer 1002 was NOT deleted
		$count = $this->_getCount("SELECT COUNT(*) FROM ohrm_project WHERE id=1002 AND is_deleted=0");
		$this->assertEquals(2, $count, "Customer 1002's projects were deleted as well!.");

    }

    /**
     * @todo Implement testGetListofCustomers().
     */
    public function testGetListofCustomers() {

       	$res = $this->classCustomer->getListofCustomers($pageNO=0,$schStr='',$mode=-1, $sortField=0, $sortOrder='ASC');
      	$this->assertNotNull($res, "record Not found");

      	$this->assertEquals(count($res), 7,'count incorrect');

      	$expected[0] = array('1001', 'zanfer1', 'forrw', '0');
      	$expected[1] = array('1002', 'zanfer2', 'forrw', '0');
      	$expected[2] = array('1003', 'zanfer3', 'forrw', '0');
      	$expected[3] = array('1004', 'zanfer4', 'forrw', '0');
      	$expected[4] = array('1005', 'zanfer5', 'forrw', '0');
      	$expected[5] = array('1006', 'zanfer6', 'forrw', '0');
      	$expected[6] = array('1007', 'zanfer7', 'forrw', '0');

      	$i= 0;

		for ($i=0; $i<count($res); $i++) {

			$this->assertSame($expected[$i][0], $res[$i][0], 'Wrong Cus Request Id');
			$this->assertSame($expected[$i][1], $res[$i][1], 'Wrong Cus Name ');
			$this->assertSame($expected[$i][2], $res[$i][2], 'Wrong Cus Name ');
      	}
    }

    /**
     * @todo Implement testFetchCustomers().
     */
	public function testFetchCustomers() {

       	$res = $this->classCustomer->fetchCustomers();
    	$this->assertNotNull($res, "record Not found");

      	$this->assertEquals(count($res), 7,'count incorrect');

      	$expected[0] = array('1001', 'zanfer1', 'forrw', '0');
      	$expected[1] = array('1002', 'zanfer2', 'forrw', '0');
      	$expected[2] = array('1003', 'zanfer3', 'forrw', '0');
      	$expected[3] = array('1004', 'zanfer4', 'forrw', '0');
      	$expected[4] = array('1005', 'zanfer5', 'forrw', '0');
      	$expected[5] = array('1006', 'zanfer6', 'forrw', '0');
      	$expected[6] = array('1007', 'zanfer7', 'forrw', '0');

      	$i= 0;

		for ($i=0; $i<count($res); $i++) {

			$this->assertSame($expected[$i][0], $res[$i]->getCustomerId(), 'Wrong Cus Request Id');
			$this->assertSame($expected[$i][1], $res[$i]->getCustomerName(), 'Wrong Cus Name ');
			$this->assertSame($expected[$i][2], $res[$i]->getCustomerDescription(), 'Wrong Cus Name ');
      	}
	}

    /**
     * @todo Implement testFetchCustomer().
     */
	public function testFetchCustomer() {

        $res  = $this->classCustomer->fetchCustomer("1005");
		$this->assertNotNull($res, "No record found");

		$this->assertEquals($res->getCustomerId(),'1005','Id Not Found');
	    $this->assertEquals($res->getCustomerName(),'zanfer5','Name Not Found');
	    $this->assertEquals($res->getCustomerDescription(),'forrw','Description Not Found');
	    $this->assertEquals($res->getCustomerStatus(), 0, 'Customer status is wrong');

	    // Fetch deleted customer
	    $this->assertTrue(mysql_query("UPDATE `ohrm_customer` SET is_deleted=1 WHERE id=1005"));

        $this->assertNull($this->classCustomer->fetchCustomer("1005"), "Deleted customer fetched");

		// Try with fetchDeleted=true
        $res = $this->classCustomer->fetchCustomer("1005", true);
		$this->assertNotNull($res, "No record found");
		$this->assertEquals($res->getCustomerId(),'1005','Id Not Found');
	    $this->assertEquals($res->getCustomerName(),'zanfer5','Name Not Found');
	    $this->assertEquals($res->getCustomerDescription(),'forrw','Description Not Found');
	    $this->assertEquals($res->getCustomerStatus(), 1, 'Customer status is wrong');

	}

	/**
	 * Run the given sql statement and return the count
	 */
	private function _getCount($countSql) {

		$result = mysql_query($countSql);
		$this->assertTrue($result !== false);
		$row = mysql_fetch_array($result, MYSQL_NUM);
		$count = $row[0];
		return $count;
	}

}

// Call CustomerTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "CustomerTest::main") {
    CustomerTest::main();
}
?>
