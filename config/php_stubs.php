<?php
/**
 * IDE Stubs for MongoDB BSON classes to resolve "Undefined type" warnings.
 * These classes are built into the 'mongodb' PHP extension.
 * This file helps your IDE (like VS Code/Intelephense) recognize them.
 */

namespace MongoDB\BSON;

if (!class_exists('MongoDB\BSON\UTCDateTime', false)) {
    /**
     * @link https://www.php.net/manual/en/class.mongodb-bson-utcdatetime.php
     */
    class UTCDateTime {
        public function __construct($milliseconds = null) {}
        public function __toString(): string { return ''; }
        public function toDateTime(): \DateTime { return new \DateTime(); }
    }
}

if (!class_exists('MongoDB\BSON\ObjectId', false)) {
    /**
     * @link https://www.php.net/manual/en/class.mongodb-bson-objectid.php
     */
    class ObjectId {
        public function __construct(?string $id = null) {}
        public function __toString(): string { return ''; }
    }
}
