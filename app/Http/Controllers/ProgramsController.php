<?php

namespace App\Http\Controllers;

// controller to test and check relationships and their inverses

class ProgramsController extends Controller {

    public function oneToOne() {    // one-to-one
        return "hello world, this is one to one relationship explained";
    }

    public function oneToMany() {   // one-to-many
        return "hello world, this is one to many function relationship";
    }

    public function manyToMany() {     // many-to-many
        return "this is many to many relationship function explanation";
    }

}
