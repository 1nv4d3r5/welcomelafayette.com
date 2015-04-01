<?php
/**
 * Created by PhpStorm.
 * User: coj
 * Date: 3/28/15
 * Time: 12:35 PM
 */

use Welcomelafayette\Lib\Config;
use Welcomelafayette\Model\Organization;

class OrganizationTest extends PHPUnit_Framework_TestCase
{
    const GENERATED_RECORDS_COUNT = 100;

    public static $APP_MODE = 'testing';

    /**
     * @var Welcomelafayette\Lib\Config
     */
    public $config;

    public function setUp()
    {
        $this->config = $this->getConfig();
    }

    /**
     * @return \Welcomelafayette\Lib\Config
     * @throws \Welcomelafayette\Lib\InvalidArgumentException
     */
    public function getConfig()
    {
        $base_path = realpath(dirname(__FILE__) . '/..');
        return Config::loadAppConfig($base_path, self::$APP_MODE);
    }


    public function generateOrgRecord()
    {
        $faker = Faker\Factory::create();
        $org = new Organization($this->getConfig());

        $this_record_vals = [
            'name' => $faker->company,
            'address1' => $faker->streetAddress,
            'address2' => $faker->buildingNumber,
            'city' => $faker->city,
            'state' => $faker->state,
            'zip' => $faker->postcode,
            'phone' => $faker->phoneNumber,
            'email' => $faker->companyEmail,
            'description' => $faker->paragraph(7),
            'img_url' => $faker->imageUrl,
            'twitter' => $faker->word,
            'facebook_url' => $faker->url,
            'website_url' => $faker->url,
            'approved' => $faker->numberBetween(0, 1),
        ];
        $id = $org->save($this_record_vals);
        return [$id, $this_record_vals];
    }


    /**
     * @return array
     */
    public function generatedRecords()
    {
        $x = 0;
        $record_vals = [];
        while ($x < self::GENERATED_RECORDS_COUNT) {
            $x++;
            $record_vals[] = $this->generateOrgRecord();
        }
        return $record_vals;
    }

    /**
     * @param $id
     * @param $expected_vals
     * @dataProvider generatedRecords
     */
    public function testGetOne($id, $expected_vals)
    {
        $org = new Organization($this->getConfig());
        $record = $org->getById($id);
        $this->assertArrayHasKey('id', $record);
        $this->assertSame($expected_vals['name'], $record['name']);
        $this->assertSame($expected_vals['address1'], $record['address1']);
        $this->assertSame($expected_vals['address2'], $record['address2']);
        $this->assertSame($expected_vals['city'], $record['city']);
        $this->assertSame($expected_vals['state'], $record['state']);
        $this->assertSame($expected_vals['zip'], $record['zip']);
        $this->assertSame($expected_vals['phone'], $record['phone']);
        $this->assertSame($expected_vals['email'], $record['email']);
        $this->assertSame($expected_vals['description'], $record['description']);
        $this->assertSame($expected_vals['img_url'], $record['img_url']);
        $this->assertSame($expected_vals['twitter'], $record['twitter']);
        $this->assertSame($expected_vals['facebook_url'], $record['facebook_url']);
        $this->assertSame($expected_vals['website_url'], $record['website_url']);
        $this->assertEquals($expected_vals['approved'], $record['approved']);
    }

    public function testGetAll()
    {
        $org = new Organization($this->getConfig());
        $org->getExtendedPdo()->query("DELETE FROM organization");

        $record_vals = [];
        for ($x = 0; $x < 5; $x++) {
            $record_vals[] = $this->generateOrgRecord();
        }

        $rows = $org->getAll();

        $this->assertSame(count($record_vals), count($rows));
    }

}