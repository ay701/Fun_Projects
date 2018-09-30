<?php
// src/Controller/SudokuController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SudokuController
{

    /**
     * @var array
     * Notes:
     * The Sudoku board could be partially filled, empty space will be filled with 0
     * An empty Sudoku board is also valid.
     * A valid Sudoku board (partially filled) is not necessarily solvable.
     * Only the filled cells need to be validated.
     *
     * Time Complexity: O(N)
     * Traverse N+N+N times = 3N => O(N)
     */

    public $grid = [];
    public $size = 0;
    public $hash = []; // Conflict check
    public $file_names = ["sampleInput_4x4.txt", "sampleInput_9x9.txt"];

    public function index()
    {
        // Get a random csv file
        $file_name = $this->getRandomFile();

        // Build grid from the csv
        $this->build_grid($file_name);

        // Check if grid input is valid
        $this->validate_grid();

        // Parse grid using hash & Generate result
        $result = $this->evaluate_grid()? 'Valid' : 'Invalid';

        return new Response('The input csv file ['.$file_name.'] is : '.$result);
    }

    private function getRandomFile()
    {
        $index = array_rand($this->file_names);
        return $this->file_names[$index];
    }


    public function build_grid($file_name)
    {

        // $fp is file pointer to file sudoku csv file
        try{
            if (($fp = fopen("../csv/".$file_name, "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                    $this->grid[] = $row;
                }
                fclose($fp);
            }
        }
        //catch exception
        catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
    }

    public function validate_grid()
    {
        $this->size = count($this->grid);

        if($this->size<4) {
            echo "Grid size needs to be larger than 4.";
            exit;
        }

        foreach($this->grid as $row){
            $row_size = count($row);

            if($row_size!=$this->size){
                echo "Each row needs to match grid size ";
                echo "(Row size: ".$row_size." detected)";
                exit;
            }
        }

        $this->square_root_validate();

    }

    /**
     * Time Complexity : O(LogN)
     * Useful for big csv input
     */
    private function square_root_validate()
    {
        // Verify quare root size using Binary search
        $start = 0;
        $end = $this->size;

        while($start<=$end){
            $mid = floor(($start+$end)/2);

            if($mid*$mid==$this->size)
                return;

            if($mid*$mid<$this->size)
                $start = $mid+1;
            else
                $end = $mid-1;
        }

        echo "Grid needs to be square root size, but ".$this->size." is given.";
        exit;
    }

    public function evaluate_grid()
    {

        // check each column
        for ($i = 0; $i<$this->size; $i++) {
            $this->init_hash();

            for ($j = 0; $j < $this->size; $j++) {
                if ($this->grid[$i][$j] != 0) {
                    $index = $this->grid[$i][$j]-1;

                    if ($this->hash[$index])
                        return false;

                    $this->hash[$index] = true;
                }
            }
	    }

        //check each row
        for ($j=0; $j<$this->size; $j++) {
            $this->init_hash();

            for ($i=0; $i<$this->size; $i++) {
                if ($this->grid[$i][$j]!=0) {
                    $index = $this->grid[$i][$j]-1;

                    if ($this->hash[$index])
                        return false;

                    $this->hash[$index] = true;
                }
            }
        }

        //check each N*N matrix
        $n = sqrt($this->size);

        for ($block=0; $block<$this->size; $block++) {
            $this->init_hash();

            for ($i=floor($block/$n)*$n; $i<floor($block/$n)*$n+$n; $i++) {
                for ($j=$block%$n*$n; $j<$block%$n*$n+$n; $j++) {
                    if ($this->grid[$i][$j]!=0) {
                        $index = $this->grid[$i][$j]-1;

                        if ($this->hash[$index])
                            return false;

                        $this->hash[$index] = true;
                    }
                }
            }
        }

	    return true;
    }

    private function init_hash()
    {
        $this->hash = [];

        for($index=0; $index<$this->size; $index++) {
            $this->hash[$index] = false;
        }
    }

}
