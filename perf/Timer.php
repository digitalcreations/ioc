<?php
class Timer {
    private $marks = array();

    public function start() {
        $this->mark("Start");
    }

    public function mark($string) {
        $this->marks[] = array("name" => $string, "time" => microtime(true), "mem" => memory_get_usage());
    }

    public function end() {
        $this->mark("End");
    }

    public function printReport() {
        $start = $this->marks[0];
        $end = $this->marks[count($this->marks)-1];

        echo "Total time used: ", $end['time'] - $start['time'], "s\n";
        echo "Total memory used: ", $end['mem'] - $start['mem'], " bytes\n";

        if (count($this->marks) == 0) return;
        echo "\nDetails: \n";
        echo "--------\n";
        echo "Label\tTime delta (s)\tMemory delta (B)\n";
        $prev = $start;
        foreach ($this->marks as $mark) {
            echo $mark['name'],"\t",number_format($mark['time'] - $prev['time'], 6),"\t",$mark['mem']-$prev['mem'],"\n";
            $prev = $mark;
        }
    }

    public static function time(callable $callback) {
        $timer = new Timer();
        $timer->start();
        call_user_func($callback, $timer);
        $timer->end();
        $timer->printReport();
    }
}