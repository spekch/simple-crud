<?php

use SimpleCrud\SimpleCrud;

class AutocreateTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $this->db = new SimpleCrud(new PDO('sqlite::memory:'));

        $this->db->executeTransaction(function ($db) {
            $db->execute(
<<<EOT
CREATE TABLE "post" (
    `id`          INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `title`       TEXT,
    `category_id` INTEGER,
    `publishedAt` TEXT,
    `isActive`    INTEGER,
    `hasContent`  INTEGER,
    `imageFile`   TEXT,
    `type`        TEXT
);
EOT
            );
        });
    }

    public function testDatabase()
    {
        $this->assertInstanceOf('SimpleCrud\\TableFactory', $this->db->getTableFactory());
        $this->assertInstanceOf('SimpleCrud\\FieldFactory', $this->db->getFieldFactory());
        $this->assertInstanceOf('SimpleCrud\\QueryFactory', $this->db->getQueryFactory());
        $this->assertInternalType('array', $this->db->getScheme());

        $this->db->setAttribute('bar', 'foo');

        $this->assertEquals('sqlite', $this->db->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->assertEquals('foo', $this->db->getAttribute('bar'));
    }

    public function testTable()
    {
        $this->assertTrue(isset($this->db->post));
        $this->assertFalse(isset($this->db->invalid));

        $post = $this->db->post;

        $this->assertInstanceOf('SimpleCrud\\Table', $post);
        $this->assertInstanceOf('SimpleCrud\\SimpleCrud', $post->getDatabase());

        $this->assertCount(8, $post->fields);
        $this->assertEquals('post', $post->name);
        $this->assertEquals($this->db->getScheme()['post'], $post->getScheme());
    }

    public function dataProviderFields()
    {
        return [
            ['id', 'Integer'],
            ['title', 'Field'],
            ['category_id', 'Integer'],
            ['publishedAt', 'Datetime'],
            ['isActive', 'Boolean'],
            ['hasContent', 'Boolean'],
            ['imageFile', 'File'],
            ['type', 'Field'],
        ];
    }

    /**
     * @dataProvider dataProviderFields
     */
    public function testFields($name, $type)
    {
        $post = $this->db->post;
        $field = $post->fields[$name];

        $this->assertInstanceOf('SimpleCrud\\Fields\\Field', $field);
        $this->assertInstanceOf('SimpleCrud\\Fields\\'.$type, $field);

        $this->assertEquals($this->db->post->getScheme()['fields'][$name], $field->getScheme());
    }
}
