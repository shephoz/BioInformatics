<?php

$alm = new Alignmemt("ATTGC", "ATGC", 2);
$alm->calc();
//$alm->showTable();
$alm->showAligned();

$alm = new Alignmemt("ACGT", "ATCCT", 2);
$alm->calc();
//$alm->showTable();
$alm->showAligned();

$alm = new Alignmemt("ATTGCACGTA", "CATGCATCCT", 2);
$alm->calc();
//$alm->showTable();
$alm->showAligned();

class Alignmemt{

	private $word1 = null;
	private $word2 = null;
	private $d = null;

	private $table = [];
	private $paths = [];

	public function __construct($word1,$word2,$d){
		$this->word1 = str_split($word1);
		$this->word2 = str_split($word2);
		array_unshift($this->word1,"");
		array_unshift($this->word2,"");
		$this->d = $d;
		for($j=0;$j<count($this->word2);$j++){
			for($i=0;$i<count($this->word1);$i++){
				$this->table[$j][$i] = ["score"=>null,"links"=>[]];
			}
		}
	}

	public function calc(){
		for($j=0;$j<count($this->word2);$j++){
			for($i=0;$i<count($this->word1);$i++){
				$this->table[$j][$i] = $this->f($i,$j);
			}
		}
	}

	private function f($i,$j){
		if($this->table[$j][$i]["score"] !== null){
			return $this->table[$j][$i];
		}else{
			if($i == 0 && $j == 0){
				return ["score" => 0, "links" => []];
			}elseif($i == 0){
				return [
					"score" => $this->f($i, $j - 1)["score"] - $this->d,
					"links" => [[$i,$j - 1]]
				];
			}elseif($j == 0){
				return [
					"score" => $this->f($i - 1, $j)["score"] - $this->d,
					"links" => [[$i - 1,$j]]
				];
			}else{
				$score = $this->s($this->word1[$i],$this->word2[$j]);
				$max_val = $this->f($i-1, $j-1)["score"]+ $score;
				$links = [[$i-1, $j-1]];

				$val = $this->f($i-1, $j)["score"] + $this->d * (-1);
				if($max_val < $val){
					$max_val = $val;
					$links = [[$i-1, $j]];
				}elseif($max_val == $val){
					$links[] = [$i-1, $j];
				}
				$val = $this->f($i, $j-1)["score"] + $this->d * (-1);
				if($max_val < $val){
					$max_val = $val;
					$links = [[$i, $j-1]];
				}elseif($max_val == $val){
					$links[] = [$i, $j-1];
				}

				return ["score"=>$max_val,"links"=>$links];
			}
		}
	}

	private function s($chr1,$chr2){
		if($chr1 == $chr2) return 2;
		else return -1;
	}

	private function link($i,$j){
		array_push($this->links,[$i,$j]);
	}

	private function getPath($payload=null){
		if($payload == null) $payload = [[count($this->word1)-1, count($this->word2)-1]];
		$head = $payload[0];
		if($head == [0,0]){
			$this->paths[] = $payload;
			return;
		}
		foreach($this->table[$head[1]][$head[0]]["links"] as $link){
			$this->getPath(array_merge([$link],$payload));
		}
	}

	public function showTable(){
		echo "<table border=1>";
		echo "<tr><th></th>";
		for($i=0;$i<count($this->word1);$i++){
			echo "<th>".$this->word1[$i]."</th>";
		}
		echo "</tr>";
		for($j=0;$j<count($this->word2);$j++){
			echo "<tr>";
			echo "<th>".$this->word2[$j]."</th>";
			for($i=0;$i<count($this->word1);$i++){
				echo "<td>".$this->table[$j][$i]["score"]."<br />";
				echo json_encode($this->table[$j][$i]["links"])."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}

	public function showAligned(){
		$this->getPath();
		$i = -1;
		$j = -1;
		foreach($this->paths as$number => $path){
			//echo json_encode($path);
			$word1 = "";
			$word2 = "";
			foreach($path as $node){
				if($node[0] == $i) $word1 .= "-";
				else $word1 .= $this->word1[$node[0]];
				$i = $node[0];
				if($node[1] == $j) $word2 .= "-";
				else $word2 .= $this->word2[$node[1]];
				$j = $node[1];
			}
			echo ($number+1).".<br />";
			echo "w1 : ".$word1."<br />";
			echo "w2 : ".$word2."<br />";
		}
	}
}
