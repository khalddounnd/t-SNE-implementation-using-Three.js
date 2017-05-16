<?php
	//error_reporting(0);
?>
<!DOCTYPE html>
<html>
	<head>
		<script src="js/three.js"></script>
		<script src="js/stats.min.js"></script>
		<script src="js/dat.gui.min.js"></script> 
		<script src="js/OrbitControls.js"></script>
		<script src="js/OBJLoader.js"></script>
		<script src="js/MTLLoader.js"></script>
		<script src="js/tsne.js"></script>
	</head>
	
	<body>
		
		<div id="dom-target" style="display: none;">
		<?php
			$i=0;
			$csv = str_getcsv(file_get_contents('Book1.csv'));
			while($csv[$i]!== null){
				echo $csv[$i];
				echo ';';
				$i++;
			}
		?>
		
		</div>
		
		
		<div id="Stats-output"></div>
	<div id="WebGL-output"></div>
	<script>
		var camera;
		var scene;
		var renderer;
		var gui = new dat.GUI({
			height : 5 * 32 - 1
		});
		var largest=10;
		var smallest = 0;
		
		
		var div = document.getElementById("dom-target");
		var myData = div.textContent;
		//window.alert(myData);
		
		var lines = myData.split(";");
		//alert(lines[0]);
		
		var axesNames = lines[0].split(" ");
		
		var sizeA = largest + 10;	
		
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
			
			//drawAxes();	
			// drawPts(10,10,10, apple);
			//drawPts(15,15,15);
			
			var pts2 = new Array();

			for(var i =1; i< lines.length-1; i++){
				
				var coordinates = lines[i].split(" ");
				var pts = new Array();
				
				for(var j = 0; j < coordinates.length-1; j++){
					
					pts.push(coordinates[j]);
				}
				
				pts2.push(pts);
				
				
			}
			
			// document.write(pts2);
			
			var tsnepts = new Array();
			
			for(var k = 0; k < pts2.length; k++){
				var pt = "[" + pts2[k].toString() + "]";
				tsnepts.push(pt);
			}
			
			// document.write(tsnepts);
			
			var opt = {}
			opt.epsilon = 10; // epsilon is learning rate (10 = default)
			opt.perplexity = 4; // roughly how many neighbors each point influences (30 = default)
			opt.dim = 3; // dimensionality of the embedding (2 = default)

			var tsne = new tsnejs.tSNE(opt); // create a tSNE instance

			//initialize data. Here we have 3 points and some example pairwise dissimilarities
			var dists = JSON.parse("[" + tsnepts + "]");
			console.log(dists);
			tsne.initDataRaw(dists);

			for(var k = 0; k < 100; k++) {
			  tsne.step(); // every time you call this, solution gets better
			}

			var Y = tsne.getSolution(); // Y is an array of 3-D points that you can plot
			
			console.log(Y);
			//document.write(Y[0]);
			
			for(var i = 0; i<Y.length; i++){
				
				var coordinates = lines[i+1].split(" ");
			
				var x = Y[i][0];
				var y = Y[i][1];
				var z = Y[i][2];
				
				var t = coordinates[coordinates.length-1];
				drawPts(x, y, z, t);
			}
			
			
			controls = new THREE.OrbitControls( camera );

			// to enable zoom
			controls.enableZoom = true;

			// to enable rotation
			controls.enableRotate = true;

			// to disable pan
			controls.enablePan = false;


			camera.position.x = sizeA+20;
			camera.position.y = sizeA+20;
			camera.position.z = sizeA+10;
			camera.lookAt(scene.position);

			var spotLight = new THREE.SpotLight( 0xffffff );
			spotLight.position.set(-40, 60, -10);
			spotLight.castShadow = true;
			spotLight.shadow.mapSize.width = 1024;
			spotLight.shadow.mapSize.height = 1024;
			scene.add(spotLight);

			
			//function to draw points
			function drawPts(x, y, z, t){
				
				if(t == "apple"){
					
				var particleMaterial = new THREE.MeshBasicMaterial();
					particleMaterial.map = THREE.ImageUtils.loadTexture('fruits/appleD.jpg');
					particleMaterial.side = THREE.DoubleSide;
			
			
					var loader = new THREE.JSONLoader();
					loader.load( 'fruits/apple.js', function ( geometry ) {
					var mesh = new THREE.Mesh( geometry, particleMaterial );

					mesh.position.x =x;
					mesh.position.y =y;
					mesh.position.z =z;
					scene.add( mesh );
					
				}); 
				} else if(t == "banana"){
					
				var particleMaterial = new THREE.MeshBasicMaterial();
				particleMaterial.map = THREE.ImageUtils.loadTexture('fruits/banana.png');
				particleMaterial.side = THREE.DoubleSide;
		
		
					var loader = new THREE.JSONLoader();
						loader.load( 'fruits/banana.js', function ( geometry ) {
						var mesh = new THREE.Mesh( geometry, particleMaterial );
						//var itmArr = [];

						mesh.position.x =x;
						mesh.position.y =y;
						mesh.position.z =z;
						scene.add( mesh );
				
					}); 
				} else if(t == "orange"){
					
					var particleMaterial = new THREE.MeshBasicMaterial();
					particleMaterial.map = THREE.ImageUtils.loadTexture('fruits/Color.jpg');
					particleMaterial.side = THREE.DoubleSide;
		
		
					var loader = new THREE.JSONLoader();
						loader.load( 'fruits/Orange.js', function ( geometry ) {
						var mesh = new THREE.Mesh( geometry, particleMaterial );
						//var itmArr = [];

						mesh.position.x =x;
						mesh.position.y =y;
						mesh.position.z =z;
						scene.add( mesh );
				
					}); 
				}
			
			}
			
			
			
			function renderScene() {
				stats.update();

				requestAnimationFrame(renderScene);
				renderer.render(scene, camera);
			}

			document.getElementById("WebGL-output").appendChild(renderer.domElement);
			renderScene();
			
			
			
		};

		function onResize() {
			camera.aspect = window.innerWidth / window.innerHeight;
			camera.updateProjectionMatrix();
			renderer.setSize(window.innerWidth, window.innerHeight);
		}
		window.onload = init;
		window.addEventListener('resize', onResize, false);
		
		
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