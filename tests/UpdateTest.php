<?php

// function updateItems ( array $ids, array $positions )
// {
	
// 	$rows = [
// 			['id' => 4451, 'position' => 0],
// 			['id' => 47751, 'position' => 0],
// 			['id' => 55714, 'position' => 3],
// 			['id' => 1234, 'position' => 0],
// 			['id' => 44716, 'position' => 0],
// 			['id' => 5218841, 'position' => 1],
// 			['id' => 871974, 'position' => 5]
// 		];
	
// 	$processing = true;
// 	$indexLocal = 0;
// 	$indexRemote = 0;
// 	$updatesList = [];
// 	while ( $processing ) {
// 		echo "{$indexLocal}:{$rows[$indexLocal]['id']} - {$indexRemote}:{$ids[$indexRemote]} ";
// 		if ( $ids[$indexRemote] == $rows[$indexLocal]['id'] ) {
// 			echo " ids are the same... ";
// 			if ($rows[$indexLocal]['position'] != 0 && 
// 				$rows[$indexLocal]['position'] != $positions[$indexRemote] ) {
// 					echo "p {$rows[$indexLocal]['position']} :: {$positions[$indexRemote]} updating ";
// 					array_push($updatesList, [$ids[$indexRemote], $positions[$indexRemote]]);
// 				}
// 			$indexLocal++;
// 		} else {
// 			echo " ids different, updating ";
// 			array_push($updatesList, [$ids[$indexRemote], $positions[$indexRemote]]);
// 			for ($iter = $indexLocal+1; $iter < count($rows); $iter++) {
// 				if ($rows[$iter]['id'] == $ids[$indexRemote]) {
// 					array_splice($rows, $iter, 1);
// 					break;
// 				}
// 			}
// 		}
// 		$indexRemote++;
// 		echo "\n";
// 		if ( $indexRemote >= count($ids) ) {
// 			$processing = false;
// 		}
// 	}
	
// 	var_dump([
// 		'indexRemote' => $indexRemote,
// 		'indexLocal' => $indexLocal,
// 		'ids' => $ids,
// 		'positions' => $positions,
// 		'updatesList' => $updatesList
// 		]);
// }

/* updateItems(
	[1234, 4451, 47751, 55714, 44716, 871974, 5218841],
	[1,2,3,4,5,6,7]
	); */



ini_set('memory_limit', '2048M');

function updateItems2 (array $ids) {
	$nonPositioned = [2118477, 1235364, 1948374, 1604241, 1866241,
					2316477, 2005737, 1908366, 1382713, 2078953,
					1920804, 2045930, 2047159, 2118478, 1777461, 2147557,
					2148589, 2206684, 2199856, 2110160, 2212799, 2264091,
					2264103, 2282456, 2264786, 2264212, 2261200, 1381057,
					2262129, 2117899, 1245226, 2282974, 2361259, 1382795,
					1396607, 1367040, 1286342, 1287328, 1422943, 1446666,
					1507634, 1500110, 1381060, 1481411, 1519456, 1517231,
					1013001, 1587670, 1587563, 1541421, 1587544, 1587714,
					1245263, 1587680, 1225653, 1609173, 1017247, 1017737,
					1011181, 1015086, 1079690, 1011173, 1028971, 1245232,
					1013986, 1012907, 1136245, 1129371, 1085631, 1245241,
					1225625, 1232069, 1224558, 1224746, 1245254];
	$positioned = [2 => 2009755, 7 => 2020106];
	
	$localIndex = 0;
	$stepSize = 1;
	$itemsToUpdate = [];
	for ( $remoteIndex = 0; $remoteIndex < count($ids); $remoteIndex++ ) {
		$positionOnScreen = $remoteIndex + 1;
		echo "{$localIndex}:{$nonPositioned[$localIndex]} - {$remoteIndex}:{$ids[$remoteIndex]}\n";
		if ( $nonPositioned[$localIndex] == $ids[$remoteIndex] ) { // position non changed
			$localIndex++;
		} else if ( isset($nonPositioned[$localIndex + 1] ) 
			&& $nonPositioned[$localIndex + 1] == $ids[$remoteIndex] ) {
				if ( isset($positioned[$localIndex]) ) {
					$positioned[-1*$localIndex] = $nonPositioned[$localIndex];
				} else {
					$positioned[$localIndex] = $nonPositioned[$localIndex];
				}
				array_splice($nonPositioned, $localIndex, 1);
				$localIndex++;
		} else  if ( isset($positioned[$positionOnScreen]) && $positioned[$positionOnScreen] == $ids[$remoteIndex] ) { // position non changed
				continue;
		} else {
			if (false !== $index = array_search($ids[$remoteIndex], $positioned)) {
				unset($positioned[$index]);
			} else {
				$index = array_search($ids[$remoteIndex], $nonPositioned);
				array_splice($nonPositioned, $index, 1);
			}
			array_push($itemsToUpdate, [$ids[$remoteIndex], $positionOnScreen]);
		}
	}
	
	var_dump($itemsToUpdate);
}




updateItems2([1287328, 2009755, 1866241, 1948374, 1604241, 1235364,
					1908366, 2020106, 2316477, 2005737, 1382713, 2078953,
					1920804, 2045930, 2047159, 2118478, 1777461, 2147557,
					2148589, 2206684, 2199856, 2110160, 2264091,
					2264103, 2282456, 2264786, 2264212, 2261200, 1381057,
					2262129, 2117899, 1245226, 1017247, 2282974, 2361259, 1382795,
					1396607, 1367040, 1286342, 2118477, 1422943, 1446666,
					1507634, 1500110, 1245232, 1481411, 1519456, 1517231,
					1013001, 1587670, 1587563, 1541421, 1587544, 1587714,
					1245263, 1587680, 1225653, 1609173, 1017737,
					1011181, 1015086, 1079690, 1011173, 1028971, 1381060,
					1013986, 1012907, 1136245, 1129371, 1085631, 1245241,
					1225625, 1232069, 1224558, 1224746, 2212799, 1245254]);

// updateItems2([2282456, 2009755, 1235364, 1866241, 1948374, 1604241, 
// 					1908366, 2020106, 2316477, 2005737, 1382713, 2078953,
// 					 2045930, 2047159, 2118478, 1777461, 2147557,
// 					2148589, 2206684, 2199856, 2110160, 2212799, 2264091,
// 					2264103, 2118477, 2264786, 1245254, 1028971, 2261200, 1381057,
// 					2262129, 2117899, 1245226, 2282974, 2361259, 1382795,
// 					1396607, 1367040, 1286342, 1287328, 1422943, 1446666,
// 					1507634, 1500110, 1381060, 1481411, 1519456, 1517231,
// 					1013001, 1587670, 1587563, 1541421, 1587544, 1587714,
// 					1245263, 1587680, 1225653, 1609173, 1017247, 1017737,
// 					1011181, 1015086, 1079690, 1011173, 1245232,
// 					1013986, 1012907, 1136245, 1129371, 1085631, 1920804, 1245241,
// 					1225625, 1232069, 1224558, 1224746, 2264212]);