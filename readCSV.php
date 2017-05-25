<?php

	$csv = str_getcsv(file_get_contents('Dataset.csv'));
	$csvdata = implode($csv, ",");
	
	$data = array();
	
	
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $csvdata) as $line){
		array_push($data, $line);
	} 
	
	$size = sizeOf($data);
	$i = 0;
	
	
?>
<!DOCTYPE html>
<html>
	<head>
		<script src="js/three.js"></script>
		<script src="js/stats.min.js"></script>
		<script src="js/dat.gui.min.js"></script> 
		<script src="js/OrbitControls.js"></script>
		<script src="js/OBJLoader.js"></script>
		<script src="js/tsne.js"></script>
	</head>
	
	<body>
		
		<div id="dom-target" style="display: none;">
		<?php
			while($i < $size){
				echo $data[$i];
				echo "\n";
				$i++;
			}
			
		?>
		
		</div>
		
		
		<div id="Stats-output"></div>
	<div id="WebGL-output"></div>
	<script>
		var camera;
		var scene;
		var items;
		var renderer;
		var largest=10;
		var smallest = 0;
		
		//fetching data from the hidden div
		var div = document.getElementById("dom-target");
		var myData = div.innerHTML;
		
		//splitting the data into multiple lines (points)
		var lines = myData.split("\n");
		lines.pop();
		
		var sizeA = largest + 10;	
		
		
		//Function for stats counter on top left of screen
		function initStats() {
			var stats = new Stats();
			stats.setMode(0);
			stats.domElement.style.position = 'absolute';
			stats.domElement.style.left = '0';
			stats.domElement.style.top = '0';
			document.getElementById("Stats-output")
			.appendChild( stats.domElement );
			return stats;
		}

		
		function init() {
			var stats = initStats();
			items = new THREE.Object3D;
			scene = new THREE.Scene();
			camera = new THREE.PerspectiveCamera(sizeA+20, window.innerWidth / window.innerHeight, 0.1, 1000);
			renderer = new THREE.WebGLRenderer();
			renderer.setClearColor(0xEEEEEEE);  // Note that the book uses setClearColorHex(), which will yield an error on recent versions of Three.js
			renderer.setSize(window.innerWidth, window.innerHeight);
			renderer.shadowMap.enabled = true;
			renderer.shadowMap.type = THREE.PCFSoftShadowMap;
			renderer.setPixelRatio(window.devicePixelRatio);
			var axes = new THREE.AxisHelper(sizeA);
			scene.add(axes);
			
			//New array to hold the points
			var pts2 = new Array();

			for(var i =1; i< lines.length-1; i++){
				
				var coordinates = lines[i].split(",");
				
				//temporary array to hold each point's dimensions as an array
				var pts = new Array();
				
				for(var j = 0; j < coordinates.length-1; j++){
					pts.push(coordinates[j]);
				}
				
				pts2.push(pts);
			}
			
			
			//final array to hold the points in the format accepted by the t-SNE function
			var tsnepts = new Array();
			
			for(var k = 0; k < pts2.length; k++){
				var pt = "[" + pts2[k].toString() + "]";
				tsnepts.push(pt);
			}
			
			
			//t-SNE options (from https://github.com/karpathy/tsnejs)
			var opt = {}
			opt.epsilon = 10; // epsilon is learning rate (10 = default)
			opt.perplexity = 30; // roughly how many neighbors each point influences (30 = default)
			opt.dim = 3; // dimensionality of the embedding (2 = default)

			var tsne = new tsnejs.tSNE(opt); // create a tSNE instance

			//initialize data
			var dists = JSON.parse("[" + tsnepts + "]");
			
			//Logging every point on console
			console.log(dists);
			tsne.initDataRaw(dists);

			for(var k = 0; k < 100; k++) {
				tsne.step(); // every time you call this, solution gets better
			}
			
			var Y = tsne.getSolution(); // Y is an array of 3-D points that you can plot
				
			console.log(Y); // Logging Y on the console to see change in dimensions
			
			//drawing the new t-SNE points
			for(var i = 0; i<Y.length-1; i++){
				
				var coordinates = lines[i+1].split(" ");
			
				var x = Y[i][0];
				var y = Y[i][1];
				var z = Y[i][2];
				drawPts(x, y, z);
			}

			// controls for viewing the graph
			controls = new THREE.OrbitControls( camera );

			// to enable zoom
			controls.enableZoom = true;

			// to enable rotation
			controls.enableRotate = true;

			// to disable pan
			controls.enablePan = false;

			//camera settings
			camera.position.x = sizeA+20;
			camera.position.y = sizeA+20;
			camera.position.z = sizeA+10;
			camera.lookAt(scene.position);

			//lighting settings
			var spotLight = new THREE.SpotLight( 0xffffff );
			spotLight.position.set(-40, 60, -10);
			spotLight.castShadow = true;
			spotLight.shadow.mapSize.width = 1024;
			spotLight.shadow.mapSize.height = 1024;
			scene.add(spotLight);

			scene.add(items);
			// drawAxes();
			
			
			
			function renderScene() {
				stats.update();

				requestAnimationFrame(renderScene);
				renderer.render(scene, camera);
			}

			document.getElementById("WebGL-output").appendChild(renderer.domElement);
			renderScene();
			
			
			
		};

		//function to handle resizing
		function onResize() {
			camera.aspect = window.innerWidth / window.innerHeight;
			camera.updateProjectionMatrix();
			renderer.setSize(window.innerWidth, window.innerHeight);
		}
		window.onload = init;
		window.addEventListener('resize', onResize, false);
		
		
		
		//function to draw points
		function drawPts(x, y, z){
				
			var geometry = new THREE.SphereGeometry( 0.02, 32, 32 );
			var material = new THREE.MeshBasicMaterial( {color: 0xff0000} );
			var sphere = new THREE.Mesh( geometry, material );
			sphere.position.x = x;
			sphere.position.y = y;
			sphere.position.z = z;
			scene.add( sphere );
		
		}
		
		
		//
		function drawAxes(){
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( i, j, 0 ),
					new THREE.Vector3( i, 0, 0 )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
			
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( i, j, 0 ),
					new THREE.Vector3( 0, j, 0 )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
			
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( i, 0, j ),
					new THREE.Vector3( 0, 0, j )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
			
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( i, 0, j ),
					new THREE.Vector3( i, 0, 0 )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
			
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( 0, i, j ),
					new THREE.Vector3( 0, i, 0 )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
			
			for(var i =0; i< sizeA; i++){
				for(var j =0; j<sizeA; j++){
					var material = new THREE.LineBasicMaterial({
						color: 0x000000
					});

					var geometry = new THREE.Geometry();
					geometry.vertices.push(
					new THREE.Vector3( 0, i, j ),
					new THREE.Vector3( 0, 0, j )
					
					);

					var line = new THREE.Line( geometry, material );
					scene.add( line );
				}
			}
		}
		
		function render() {
			requestAnimationFrame(render);
			controls.update();
			renderer.render(scene, camera);
		}
		
	</script>
	</body>
</html>