<?php
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    // SSL connection

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Suhin</title>
<link rel="icon" href="icon_suhin.png" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="gl-matrix.js"></script>
</head>
<body id="page" style="margin:0;">

<!--
- Idea of a swimming cube for Swim (English world)
- TAD-1001 is now version 1.2.1
- Idea of WAD-50 (a level editor) WAD = Why A Dee ~= Map Editor
-->

<audio id="music_peace" loop="loop" preload="auto">
<source src="ressources/futurebelongstolove.mp3" type="audio/mpeg" />
<source src="ressources/futurebelongstolove.ogg" type="audio/ogg" />
<source src="ressources/futurebelongstolove.wav" type="audio/wav" />
</audio>

<audio id="music_menu" loop="loop" preload="auto">
<source src="ressources/decisions.mp3" type="audio/mpeg" />
<source src="ressources/decisions.ogg" type="audio/ogg" />
<source src="ressources/decisions.wav" type="audio/wav" />
</audio>

<audio id="language_description_english_daniel_meilland" preload="auto">
<source src="ressources/english_speakers/daniel_meilland/language_description.mp3" type="audio/mpeg" />
<source src="ressources/english_speakers/daniel_meilland/language_description.ogg" type="audio/ogg" />
<source src="ressources/english_speakers/daniel_meilland/language_description.wav" type="audio/wav" />
</audio>

<audio id="storyofsounds_english_daniel_meilland_-a" preload="auto">
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a.mp3" type="audio/mpeg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a.ogg" type="audio/ogg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a.wav" type="audio/wav" />
</audio>

<audio id="storyofsounds_english_daniel_meilland_-a_e" preload="auto">
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a_e.mp3" type="audio/mpeg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a_e.ogg" type="audio/ogg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-a_e.wav" type="audio/wav" />
</audio>

<audio id="storyofsounds_english_daniel_meilland_-e" preload="auto">
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e.mp3" type="audio/mpeg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e.ogg" type="audio/ogg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e.wav" type="audio/wav" />
</audio>

<audio id="storyofsounds_english_daniel_meilland_-e_e" preload="auto">
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e_e.mp3" type="audio/mpeg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e_e.ogg" type="audio/ogg" />
<source src="ressources/english_speakers/daniel_meilland/storyofsounds/-e_e.wav" type="audio/wav" />
</audio>



<canvas width="800" height="450" id="game3d" style="display: block; margin: auto;">Your browser does not support the canvas element.</canvas>
<canvas width="800" height="450" id="game" style="display: block; margin: auto;">Your browser does not support the canvas element.</canvas>


<image id="crate-image" src="ressources/black_bg_yellow_fill_a.png" width="0" height="0"></image>

<script>
three_dimensions_shown = false;
craftmans_solution = 0.001;

canvas = document.getElementById('game');
//canvas3d = document.getElementById('game3d');
body = document.getElementById("page");
context = canvas.getContext('2d');
//context3d = canvas3d.getContext('webgl');

vertexShaderText = 
[
'precision mediump float;',
'',
'attribute vec3 vertPosition;',
'attribute vec2 vertTexCoord;',
'varying vec2 fragTexCoord;',
'uniform mat4 mWorld;',
'uniform mat4 mView;',
'uniform mat4 mProj;',
'',
'void main()',
'{',
'  fragTexCoord = vertTexCoord;',
'  gl_Position = mProj * mView * mWorld * vec4(vertPosition, 1.0);',
'}'
].join('\n');

fragmentShaderText =
[
'precision mediump float;',
'',
'varying vec2 fragTexCoord;',
'uniform sampler2D sampler;',
'',
'void main()',
'{',
'  gl_FragColor = texture2D(sampler, fragTexCoord);',
'}'
].join('\n');


	canvas3d = document.getElementById('game3d');
	gl = canvas3d.getContext('webgl');

	if (!gl) {
		console.log('WebGL not supported, falling back on experimental-webgl');
		gl = canvas3d.getContext('experimental-webgl');
	}

	if (!gl) {
		alert('Your browser does not support WebGL');
	}

	gl.clearColor(0.75, 0.85, 0.8, 1.0);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
	gl.enable(gl.DEPTH_TEST);
	gl.enable(gl.CULL_FACE);
	gl.frontFace(gl.CCW);
	gl.cullFace(gl.BACK);

	//
	// Create shaders
	// 
	vertexShader = gl.createShader(gl.VERTEX_SHADER);
	fragmentShader = gl.createShader(gl.FRAGMENT_SHADER);

	gl.shaderSource(vertexShader, vertexShaderText);
	gl.shaderSource(fragmentShader, fragmentShaderText);

	gl.compileShader(vertexShader);
	if (!gl.getShaderParameter(vertexShader, gl.COMPILE_STATUS)) {
		console.error('ERROR compiling vertex shader!', gl.getShaderInfoLog(vertexShader));
	}

	gl.compileShader(fragmentShader);
	if (!gl.getShaderParameter(fragmentShader, gl.COMPILE_STATUS)) {
		console.error('ERROR compiling fragment shader!', gl.getShaderInfoLog(fragmentShader));
	}

	program = gl.createProgram();
	gl.attachShader(program, vertexShader);
	gl.attachShader(program, fragmentShader);
	gl.linkProgram(program);
	if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
		console.error('ERROR linking program!', gl.getProgramInfoLog(program));
	}
	gl.validateProgram(program);
	if (!gl.getProgramParameter(program, gl.VALIDATE_STATUS)) {
		console.error('ERROR validating program!', gl.getProgramInfoLog(program));
	}

	//
	// Create buffer
	//
	boxVertices = 
	[ // X, Y, Z           U, V
		// Top
		-1.0, 1.0, -1.0,   0, 0,
		-1.0, 1.0, 1.0,    0, 1,
		1.0, 1.0, 1.0,     1, 1,
		1.0, 1.0, -1.0,    1, 0,

		// Left
		-1.0, 1.0, 1.0,    0, 0,
		-1.0, -1.0, 1.0,   1, 0,
		-1.0, -1.0, -1.0,  1, 1,
		-1.0, 1.0, -1.0,   0, 1,

		// Right
		1.0, 1.0, 1.0,    1, 1,
		1.0, -1.0, 1.0,   0, 1,
		1.0, -1.0, -1.0,  0, 0,
		1.0, 1.0, -1.0,   1, 0,

		// Front
		1.0, 1.0, 1.0,    1, 1,
		1.0, -1.0, 1.0,    1, 0,
		-1.0, -1.0, 1.0,    0, 0,
		-1.0, 1.0, 1.0,    0, 1,

		// Back
		1.0, 1.0, -1.0,    0, 0,
		1.0, -1.0, -1.0,    0, 1,
		-1.0, -1.0, -1.0,    1, 1,
		-1.0, 1.0, -1.0,    1, 0,

		// Bottom
		-1.0, -1.0, -1.0,   1, 1,
		-1.0, -1.0, 1.0,    1, 0,
		1.0, -1.0, 1.0,     0, 0,
		1.0, -1.0, -1.0,    0, 1,
	];

	var boxIndices =
	[
		// Top
		0, 1, 2,
		0, 2, 3,

		// Left
		5, 4, 6,
		6, 4, 7,

		// Right
		8, 9, 10,
		8, 10, 11,

		// Front
		13, 12, 14,
		15, 14, 12,

		// Back
		16, 17, 18,
		16, 18, 19,

		// Bottom
		21, 20, 22,
		22, 20, 23
	];

	boxVertexBufferObject = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, boxVertexBufferObject);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(boxVertices), gl.STATIC_DRAW);

	boxIndexBufferObject = gl.createBuffer();
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, boxIndexBufferObject);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(boxIndices), gl.STATIC_DRAW);

	positionAttribLocation = gl.getAttribLocation(program, 'vertPosition');
	texCoordAttribLocation = gl.getAttribLocation(program, 'vertTexCoord');
	gl.vertexAttribPointer(
		positionAttribLocation, // Attribute location
		3, // Number of elements per attribute
		gl.FLOAT, // Type of elements
		gl.FALSE,
		5 * Float32Array.BYTES_PER_ELEMENT, // Size of an individual vertex
		0 // Offset from the beginning of a single vertex to this attribute
	);
	gl.vertexAttribPointer(
		texCoordAttribLocation, // Attribute location
		2, // Number of elements per attribute
		gl.FLOAT, // Type of elements
		gl.FALSE,
		5 * Float32Array.BYTES_PER_ELEMENT, // Size of an individual vertex
		3 * Float32Array.BYTES_PER_ELEMENT // Offset from the beginning of a single vertex to this attribute
	);

	gl.enableVertexAttribArray(positionAttribLocation);
	gl.enableVertexAttribArray(texCoordAttribLocation);

	//
	// Create texture
	//
	boxTexture = gl.createTexture();
	gl.bindTexture(gl.TEXTURE_2D, boxTexture);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
	gl.texImage2D(
		gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA,
		gl.UNSIGNED_BYTE,
		document.getElementById('crate-image')
	);
	gl.bindTexture(gl.TEXTURE_2D, null);

	// Tell OpenGL state machine which program should be active.
	gl.useProgram(program);

	matWorldUniformLocation = gl.getUniformLocation(program, 'mWorld');
	matViewUniformLocation = gl.getUniformLocation(program, 'mView');
	matProjUniformLocation = gl.getUniformLocation(program, 'mProj');

	worldMatrix = new Float32Array(16);
	viewMatrix = new Float32Array(16);
	projMatrix = new Float32Array(16);
	mat4.identity(worldMatrix);
	mat4.lookAt(viewMatrix, [0, 0, -8], [0, 0, 0], [0, 1, 0]);
	mat4.perspective(projMatrix, glMatrix.toRadian(45), canvas.clientWidth / canvas.clientHeight, 0.1, 1000.0);

	gl.uniformMatrix4fv(matWorldUniformLocation, gl.FALSE, worldMatrix);
	gl.uniformMatrix4fv(matViewUniformLocation, gl.FALSE, viewMatrix);
	gl.uniformMatrix4fv(matProjUniformLocation, gl.FALSE, projMatrix);

	xRotationMatrix = new Float32Array(16);
	yRotationMatrix = new Float32Array(16);
/*
	//
	// Main render loop
	//
	*/identityMatrix = new Float32Array(16);
	mat4.identity(identityMatrix);
	angle = 0;/*
	var loop = function () {
		angle = performance.now() / 1000 / 6 * 2 * Math.PI;
		mat4.rotate(yRotationMatrix, identityMatrix, angle, [0, 1, 0]);
		mat4.rotate(xRotationMatrix, identityMatrix, angle / 4, [1, 0, 0]);
		mat4.mul(worldMatrix, yRotationMatrix, xRotationMatrix);
		gl.uniformMatrix4fv(matWorldUniformLocation, gl.FALSE, worldMatrix);

		gl.clearColor(0.75, 0.85, 0.8, 1.0);
		gl.clear(gl.DEPTH_BUFFER_BIT | gl.COLOR_BUFFER_BIT);

		gl.bindTexture(gl.TEXTURE_2D, boxTexture);
		gl.activeTexture(gl.TEXTURE0);

		gl.drawElements(gl.TRIANGLES, boxIndices.length, gl.UNSIGNED_SHORT, 0);

		requestAnimationFrame(loop);
	};
	requestAnimationFrame(loop);*/
	


function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

key_freshly_pressed = new Array();
key_pressed = new Array();
key_freshly_released = new Array();
clicking = false;
clicking_x = 0;
clicking_y = 0;

document.addEventListener("keydown", function (e) {
	e.preventDefault();
	
	if (key_pressed[e.code] != true) {
		key_freshly_pressed[e.code] = true;
	}
	key_pressed[e.code] = true;
});

document.addEventListener("keyup", function (e) {
	e.preventDefault();
	
	key_pressed[e.code] = false;
	key_freshly_released[e.code] = true;
});

canvas.addEventListener('click', function(e) { 
	clicking_x = e.pageX - canvas.offsetLeft;
	clicking_y = e.pageY - canvas.offsetTop;
	clicking = true;
}, false);



world_of_english  = "R气气气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "uDS气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "noe气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "Wuc气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "abo气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "lln气气气气气气气气气气气气气气气气气气气气气气气气气气气气气\n";
world_of_english += "ledPrimetown气气气气气气气气气Fontzone气气气\n";
world_of_english += "jjjWalkWalkWFlowerGasW气气气气气气气气气气\n";
world_of_english += "uuuVoidEEEEaSwimWatera气气气气气气气气气气\n";
world_of_english += "mmmVoidnnnnlSinkDrownl气气气气气气气气气气\n";
world_of_english += "pppWalkddddkTopBottoml气气气气气气气气气气\n";
world_of_english += "SSkySSSSkySkySkyCloudAirM气气气气气气气\n";
world_of_english += "tSkykkkSkySkySkyFreedom气a气气气气气气气\n";
world_of_english += "oSkyyyy气气OAGSFSkyFreedomp气气气气气气气\n";
world_of_english += "nCloudGasaiakiDoublejumpl气气气气气气气\n";
world_of_english += "e始FreedomkrsyrDictionarye气气气气气气气\n";
world_of_english += "StoneWallFenceSolidJumpRockBlock";

/* 
#0 Start of the players (additional players go on top)
始 S-t-a-r-t

#1 Nothing:
气 G-a-s
Gas
Air
Sky
Void
Cloud
Flower
Freedom
Rainbow
Doublejump
Secondjump

#2 Solid:
Run
Top
Jump
Rock
Roof
Wall
Walk
Block
Fence
Solid
Stone
Bottom
Barrier
Walljump
You can walk on it
You can jump from it


#3 Water:
Swim
Sink
Water
Drown
Swimmingpool

#4 Death:
End
Death
Return
Return to the start

#5 Oak (chêne, дъб)
#6 Fir (sapin, елка)
#7 Maple (érable, клен)

#8 Primetown
*/



english_original_positions = world_of_english.split("\n");
for (i = 0; i < english_original_positions.length; i++) {
english_original_positions[i] = english_original_positions[i].split("");
}

english_background_color_used_red = [];
english_background_color_used_green = [];
english_background_color_used_blue = [];
english_text_color_used_red = [];
english_text_color_used_green = [];
english_text_color_used_blue = [];
english_barrier_color_used_red = [];
english_barrier_color_used_green = [];
english_barrier_color_used_blue = [];
english_code_used = [];
english_top_barrier = []; //TRUE or FALSE
english_bottom_barrier = [];
english_left_barrier = [];
english_right_barrier = [];

for (i = 0; i < english_original_positions.length; i++) {
	english_background_color_used_red[i] = [];
	english_background_color_used_green[i] = [];
	english_background_color_used_blue[i] = [];
	english_text_color_used_red[i] = [];
	english_text_color_used_green[i] = [];
	english_text_color_used_blue[i] = [];
	english_barrier_color_used_red[i] = [];
	english_barrier_color_used_green[i] = [];
	english_barrier_color_used_blue[i] = [];
	english_code_used[i] = [];
	english_top_barrier[i] = [];
	english_bottom_barrier[i] = [];
	english_left_barrier[i] = [];
	english_right_barrier[i] = [];
	
	for (j = 0; j < english_original_positions[i].length; j++) {
		english_background_color_used_red[i][j] = 0;
		english_background_color_used_green[i][j] = 0;
		english_background_color_used_blue[i][j] = 0;
	}
}




for (i = 0; i < english_original_positions.length; i++) {
	for (j = 0; j < english_original_positions[i].length; j++) {
		if (english_original_positions[i][j] == "G" && english_original_positions[i][j + 1] == "a" && english_original_positions[i][j + 2] == "s") {
			console.log("Hi!" + i + "; " + j);
		
			english_code_used[i][j] = 1;
			english_code_used[i][j + 1] = 1;
			english_code_used[i][j + 2] = 1;
			
			english_background_color_used_red[i][j] = 0;
			english_background_color_used_red[i][j + 1] = 0;
			english_background_color_used_red[i][j + 2] = 0;
			english_background_color_used_green[i][j] = 255;
			english_background_color_used_green[i][j + 1] = 255;
			english_background_color_used_green[i][j + 2] = 255;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_green[i][j] = 0;
			english_text_color_used_green[i][j + 1] = 0;
			english_text_color_used_green[i][j + 2] = 0;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_green[i][j] = 0;
			english_barrier_color_used_green[i][j + 1] = 0;
			english_barrier_color_used_green[i][j + 2] = 0;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = true;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
		/*} else if (english_original_positions[i][j] == "D" && english_original_positions[i + 1][j] == "o" && english_original_positions[i + 2][j] == "u" && english_original_positions[i + 3][j] == "b" && english_original_positions[i + 4][j] == "l" && english_original_positions[i + 5][j] == "e") {
			console.log("Hi!" + i + "; " + j);
		
			english_code_used[i][j] = 1;
			english_code_used[i + 1][j] = 1;
			english_code_used[i + 2][j] = 1;
			
			english_background_color_used_red[i][j] = 0;
			english_background_color_used_red[i][j + 1] = 0;
			english_background_color_used_red[i][j + 2] = 0;
			english_background_color_used_green[i][j] = 255;
			english_background_color_used_green[i][j + 1] = 255;
			english_background_color_used_green[i][j + 2] = 255;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_green[i][j] = 0;
			english_text_color_used_green[i][j + 1] = 0;
			english_text_color_used_green[i][j + 2] = 0;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_green[i][j] = 0;
			english_barrier_color_used_green[i][j + 1] = 0;
			english_barrier_color_used_green[i][j + 2] = 0;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = true;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;*/
		} else if (english_original_positions[i][j] == "D" && english_original_positions[i + 1][j] == "o" && english_original_positions[i + 2][j] == "u" && english_original_positions[i + 3][j] == "b" && english_original_positions[i + 4][j] == "l" && english_original_positions[i + 5][j] == "e" && english_original_positions[i + 6][j] == "j" && english_original_positions[i + 7][j] == "u" && english_original_positions[i + 8][j] == "m" && english_original_positions[i + 9][j] == "p") {
			console.log("Hi!" + i + "; " + j);
		
			english_code_used[i][j] = 1;
			english_code_used[i + 1][j] = 1;
			english_code_used[i + 2][j] = 1;
			english_code_used[i + 3][j] = 1;
			english_code_used[i + 4][j] = 1;
			english_code_used[i + 5][j] = 1;
			english_code_used[i + 6][j] = 1;
			english_code_used[i + 7][j] = 1;
			english_code_used[i + 8][j] = 1;
			english_code_used[i + 9][j] = 1;
			
			english_background_color_used_red[i][j] = 0;
			english_background_color_used_red[i + 1][j] = 0;
			english_background_color_used_red[i + 2][j] = 0;
			english_background_color_used_red[i + 3][j] = 0;
			english_background_color_used_red[i + 4][j] = 0;
			english_background_color_used_red[i + 5][j] = 0;
			english_background_color_used_red[i + 6][j] = 0;
			english_background_color_used_red[i + 7][j] = 0;
			english_background_color_used_red[i + 8][j] = 0;
			english_background_color_used_red[i + 9][j] = 0;
			english_background_color_used_green[i][j] = 255;
			english_background_color_used_green[i + 1][j] = 255;
			english_background_color_used_green[i + 2][j] = 255;
			english_background_color_used_green[i + 3][j] = 255;
			english_background_color_used_green[i + 4][j] = 255;
			english_background_color_used_green[i + 5][j] = 255;
			english_background_color_used_green[i + 6][j] = 255;
			english_background_color_used_green[i + 7][j] = 255;
			english_background_color_used_green[i + 8][j] = 255;
			english_background_color_used_green[i + 9][j] = 255;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i + 1][j] = 255;
			english_background_color_used_blue[i + 2][j] = 255;
			english_background_color_used_blue[i + 3][j] = 255;
			english_background_color_used_blue[i + 4][j] = 255;
			english_background_color_used_blue[i + 5][j] = 255;
			english_background_color_used_blue[i + 6][j] = 255;
			english_background_color_used_blue[i + 7][j] = 255;
			english_background_color_used_blue[i + 8][j] = 255;
			english_background_color_used_blue[i + 9][j] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i + 1][j] = 0;
			english_text_color_used_red[i + 2][j] = 0;
			english_text_color_used_red[i + 3][j] = 0;
			english_text_color_used_red[i + 4][j] = 0;
			english_text_color_used_red[i + 5][j] = 0;
			english_text_color_used_red[i + 6][j] = 0;
			english_text_color_used_red[i + 7][j] = 0;
			english_text_color_used_red[i + 8][j] = 0;
			english_text_color_used_red[i + 9][j] = 0;
			english_text_color_used_green[i][j] = 0;
			english_text_color_used_green[i + 1][j] = 0;
			english_text_color_used_green[i + 2][j] = 0;
			english_text_color_used_green[i + 3][j] = 0;
			english_text_color_used_green[i + 4][j] = 0;
			english_text_color_used_green[i + 5][j] = 0;
			english_text_color_used_green[i + 6][j] = 0;
			english_text_color_used_green[i + 7][j] = 0;
			english_text_color_used_green[i + 8][j] = 0;
			english_text_color_used_green[i + 9][j] = 0;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i + 1][j] = 255;
			english_text_color_used_blue[i + 2][j] = 255;
			english_text_color_used_blue[i + 3][j] = 255;
			english_text_color_used_blue[i + 4][j] = 255;
			english_text_color_used_blue[i + 5][j] = 255;
			english_text_color_used_blue[i + 6][j] = 255;
			english_text_color_used_blue[i + 7][j] = 255;
			english_text_color_used_blue[i + 8][j] = 255;
			english_text_color_used_blue[i + 9][j] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i + 1][j] = 0;
			english_barrier_color_used_red[i + 2][j] = 0;
			english_barrier_color_used_red[i + 3][j] = 0;
			english_barrier_color_used_red[i + 4][j] = 0;
			english_barrier_color_used_red[i + 5][j] = 0;
			english_barrier_color_used_red[i + 6][j] = 0;
			english_barrier_color_used_red[i + 7][j] = 0;
			english_barrier_color_used_red[i + 8][j] = 0;
			english_barrier_color_used_red[i + 9][j] = 0;
			english_barrier_color_used_green[i][j] = 0;
			english_barrier_color_used_green[i + 1][j] = 0;
			english_barrier_color_used_green[i + 2][j] = 0;
			english_barrier_color_used_green[i + 3][j] = 0;
			english_barrier_color_used_green[i + 4][j] = 0;
			english_barrier_color_used_green[i + 5][j] = 0;
			english_barrier_color_used_green[i + 6][j] = 0;
			english_barrier_color_used_green[i + 7][j] = 0;
			english_barrier_color_used_green[i + 8][j] = 0;
			english_barrier_color_used_green[i + 9][j] = 0;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i + 1][j] = 255;
			english_barrier_color_used_blue[i + 2][j] = 255;
			english_barrier_color_used_blue[i + 3][j] = 255;
			english_barrier_color_used_blue[i + 4][j] = 255;
			english_barrier_color_used_blue[i + 5][j] = 255;
			english_barrier_color_used_blue[i + 6][j] = 255;
			english_barrier_color_used_blue[i + 7][j] = 255;
			english_barrier_color_used_blue[i + 8][j] = 255;
			english_barrier_color_used_blue[i + 9][j] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = true;
			english_bottom_barrier[i][j] = false;
			english_left_barrier[i][j] = true;
			english_top_barrier[i + 1][j] = false;
			english_right_barrier[i + 1][j] = true;
			english_bottom_barrier[i + 1][j] = false;
			english_left_barrier[i + 1][j] = true;
			english_top_barrier[i + 2][j] = false;
			english_right_barrier[i + 2][j] = true;
			english_bottom_barrier[i + 2][j] = false;
			english_left_barrier[i + 2][j] = true;
			english_top_barrier[i + 3][j] = false;
			english_right_barrier[i + 3][j] = true;
			english_bottom_barrier[i + 3][j] = false;
			english_left_barrier[i + 3][j] = true;
			english_top_barrier[i + 4][j] = false;
			english_right_barrier[i + 4][j] = true;
			english_bottom_barrier[i + 4][j] = false;
			english_left_barrier[i + 4][j] = true;
			english_top_barrier[i + 5][j] = false;
			english_right_barrier[i + 5][j] = true;
			english_bottom_barrier[i + 5][j] = false;
			english_left_barrier[i + 5][j] = true;
			english_top_barrier[i + 6][j] = false;
			english_right_barrier[i + 6][j] = true;
			english_bottom_barrier[i + 6][j] = false;
			english_left_barrier[i + 6][j] = true;
			english_top_barrier[i + 7][j] = false;
			english_right_barrier[i + 7][j] = true;
			english_bottom_barrier[i + 7][j] = false;
			english_left_barrier[i + 7][j] = true;
			english_top_barrier[i + 8][j] = false;
			english_right_barrier[i + 8][j] = true;
			english_bottom_barrier[i + 8][j] = false;
			english_left_barrier[i + 8][j] = true;
			english_top_barrier[i][j + 9] = false;
			english_right_barrier[i][j + 9] = true;
			english_bottom_barrier[i][j + 9] = true;
			english_left_barrier[i][j + 9] = true;
		} else if (english_original_positions[i][j] == "W" && english_original_positions[i][j + 1] == "a" && english_original_positions[i][j + 2] == "t" && english_original_positions[i][j + 3] == "e" && english_original_positions[i][j + 4] == "r") {
			english_code_used[i][j] = 3;
			english_code_used[i][j + 1] = 3;
			english_code_used[i][j + 2] = 3;
			english_code_used[i][j + 3] = 3;
			english_code_used[i][j + 4] = 3;
			
			if (english_code_used[i - 1][j] == 3) {
				english_background_color_used_red[i][j] = 0;
				english_background_color_used_red[i][j + 1] = 0;
				english_background_color_used_red[i][j + 2] = 0;
				english_background_color_used_red[i][j + 3] = 0;
				english_background_color_used_red[i][j + 4] = 0;
			} else { // Waves
				english_background_color_used_red[i][j] = -1;
				english_background_color_used_red[i][j + 1] = -1;
				english_background_color_used_red[i][j + 2] = -1;
				english_background_color_used_red[i][j + 3] = -1;
				english_background_color_used_red[i][j + 4] = -1;
			}
			english_background_color_used_green[i][j] = 0;
			english_background_color_used_green[i][j + 1] = 0;
			english_background_color_used_green[i][j + 2] = 0;
			english_background_color_used_green[i][j + 3] = 0;
			english_background_color_used_green[i][j + 4] = 0;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			english_background_color_used_blue[i][j + 3] = 255;
			english_background_color_used_blue[i][j + 4] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_red[i][j + 3] = 0;
			english_text_color_used_red[i][j + 4] = 0;
			english_text_color_used_green[i][j] = 255;
			english_text_color_used_green[i][j + 1] = 255;
			english_text_color_used_green[i][j + 2] = 255;
			english_text_color_used_green[i][j + 3] = 255;
			english_text_color_used_green[i][j + 4] = 255;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			english_text_color_used_blue[i][j + 3] = 255;
			english_text_color_used_blue[i][j + 4] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_red[i][j + 3] = 0;
			english_barrier_color_used_red[i][j + 4] = 0;
			english_barrier_color_used_green[i][j] = 255;
			english_barrier_color_used_green[i][j + 1] = 255;
			english_barrier_color_used_green[i][j + 2] = 255;
			english_barrier_color_used_green[i][j + 3] = 255;
			english_barrier_color_used_green[i][j + 4] = 255;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			english_barrier_color_used_blue[i][j + 3] = 255;
			english_barrier_color_used_blue[i][j + 4] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = false;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
			english_top_barrier[i][j + 3] = true;
			english_right_barrier[i][j + 3] = false;
			english_bottom_barrier[i][j + 3] = true;
			english_left_barrier[i][j + 3] = false;
			english_top_barrier[i][j + 4] = true;
			english_right_barrier[i][j + 4] = true;
			english_bottom_barrier[i][j + 4] = true;
			english_left_barrier[i][j + 4] = false;
		} else if (english_original_positions[i][j] == "S" && english_original_positions[i][j + 1] == "w" && english_original_positions[i][j + 2] == "i" && english_original_positions[i][j + 3] == "m") {
			english_code_used[i][j] = 3;
			english_code_used[i][j + 1] = 3;
			english_code_used[i][j + 2] = 3;
			english_code_used[i][j + 3] = 3;
			
			if (english_code_used[i - 1][j] == 3) {
				english_background_color_used_red[i][j] = 0;
				english_background_color_used_red[i][j + 1] = 0;
				english_background_color_used_red[i][j + 2] = 0;
				english_background_color_used_red[i][j + 3] = 0;
			} else { // Waves
				english_background_color_used_red[i][j] = -1;
				english_background_color_used_red[i][j + 1] = -1;
				english_background_color_used_red[i][j + 2] = -1;
				english_background_color_used_red[i][j + 3] = -1;
			}
			english_background_color_used_green[i][j] = 0;
			english_background_color_used_green[i][j + 1] = 0;
			english_background_color_used_green[i][j + 2] = 0;
			english_background_color_used_green[i][j + 3] = 0;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			english_background_color_used_blue[i][j + 3] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_red[i][j + 3] = 0;
			english_text_color_used_green[i][j] = 255;
			english_text_color_used_green[i][j + 1] = 255;
			english_text_color_used_green[i][j + 2] = 255;
			english_text_color_used_green[i][j + 3] = 255;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			english_text_color_used_blue[i][j + 3] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_red[i][j + 3] = 0;
			english_barrier_color_used_green[i][j] = 255;
			english_barrier_color_used_green[i][j + 1] = 255;
			english_barrier_color_used_green[i][j + 2] = 255;
			english_barrier_color_used_green[i][j + 3] = 255;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			english_barrier_color_used_blue[i][j + 3] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = false;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
			english_top_barrier[i][j + 3] = true;
			english_right_barrier[i][j + 3] = true;
			english_bottom_barrier[i][j + 3] = true;
			english_left_barrier[i][j + 3] = false;
		} else if (english_original_positions[i][j] == "P" && english_original_positions[i][j + 1] == "r" && english_original_positions[i][j + 2] == "i" && english_original_positions[i][j + 3] == "m" && english_original_positions[i][j + 4] == "e" && english_original_positions[i][j + 5] == "t" && english_original_positions[i][j + 6] == "o" && english_original_positions[i][j + 7] == "w" && english_original_positions[i][j + 8] == "n") {
			english_code_used[i][j] = 8;
			english_code_used[i][j + 1] = 8;
			english_code_used[i][j + 2] = 8;
			english_code_used[i][j + 3] = 8;
			english_code_used[i][j + 4] = 8;
			english_code_used[i][j + 5] = 8;
			english_code_used[i][j + 6] = 8;
			english_code_used[i][j + 7] = 8;
			english_code_used[i][j + 8] = 8;
			
			english_background_color_used_red[i][j] = -2;/*
			english_background_color_used_red[i][j + 1] = -2;
			english_background_color_used_red[i][j + 2] = -2;
			english_background_color_used_red[i][j + 3] = -2;
			english_background_color_used_red[i][j + 4] = -2;
			english_background_color_used_red[i][j + 5] = -2;
			english_background_color_used_red[i][j + 6] = -2;
			english_background_color_used_red[i][j + 7] = -2;
			english_background_color_used_red[i][j + 8] = -2;*/
				
			english_background_color_used_green[i][j] = 0;
			english_background_color_used_green[i][j + 1] = 0;
			english_background_color_used_green[i][j + 2] = 0;
			english_background_color_used_green[i][j + 3] = 0;
			english_background_color_used_green[i][j + 4] = 0;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			english_background_color_used_blue[i][j + 3] = 255;
			english_background_color_used_blue[i][j + 4] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_red[i][j + 3] = 0;
			english_text_color_used_red[i][j + 4] = 0;
			english_text_color_used_green[i][j] = 255;
			english_text_color_used_green[i][j + 1] = 255;
			english_text_color_used_green[i][j + 2] = 255;
			english_text_color_used_green[i][j + 3] = 255;
			english_text_color_used_green[i][j + 4] = 255;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			english_text_color_used_blue[i][j + 3] = 255;
			english_text_color_used_blue[i][j + 4] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_red[i][j + 3] = 0;
			english_barrier_color_used_red[i][j + 4] = 0;
			english_barrier_color_used_green[i][j] = 255;
			english_barrier_color_used_green[i][j + 1] = 255;
			english_barrier_color_used_green[i][j + 2] = 255;
			english_barrier_color_used_green[i][j + 3] = 255;
			english_barrier_color_used_green[i][j + 4] = 255;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			english_barrier_color_used_blue[i][j + 3] = 255;
			english_barrier_color_used_blue[i][j + 4] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = false;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
			english_top_barrier[i][j + 3] = true;
			english_right_barrier[i][j + 3] = false;
			english_bottom_barrier[i][j + 3] = true;
			english_left_barrier[i][j + 3] = false;
			english_top_barrier[i][j + 4] = true;
			english_right_barrier[i][j + 4] = true;
			english_bottom_barrier[i][j + 4] = true;
			english_left_barrier[i][j + 4] = false;
		} else if (english_original_positions[i][j] == "气") {
			english_code_used[i][j] = 1;
			
			english_background_color_used_red[i][j] = 0;
			english_background_color_used_green[i][j] = 255;
			english_background_color_used_blue[i][j] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_green[i][j] = 0;
			english_text_color_used_blue[i][j] = 255;
			
			english_barrier_color_used_red[i][j] = 0;;
			english_barrier_color_used_green[i][j] = 0;
			english_barrier_color_used_blue[i][j] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = true;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
		} else if (english_original_positions[i][j] == "S" && english_original_positions[i][j + 1] == "i" && english_original_positions[i][j + 2] == "n" && english_original_positions[i][j + 3] == "k") {
			english_code_used[i][j] = 3;
			english_code_used[i][j + 1] = 3;
			english_code_used[i][j + 2] = 3;
			english_code_used[i][j + 3] = 3;
			
			if (english_code_used[i - 1][j] == 3) {
				english_background_color_used_red[i][j] = 0;
				english_background_color_used_red[i][j + 1] = 0;
				english_background_color_used_red[i][j + 2] = 0;
				english_background_color_used_red[i][j + 3] = 0;
			} else { // Waves
				english_background_color_used_red[i][j] = -1;
				english_background_color_used_red[i][j + 1] = -1;
				english_background_color_used_red[i][j + 2] = -1;
				english_background_color_used_red[i][j + 3] = -1;
			}
			english_background_color_used_green[i][j] = 0;
			english_background_color_used_green[i][j + 1] = 0;
			english_background_color_used_green[i][j + 2] = 0;
			english_background_color_used_green[i][j + 3] = 0;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			english_background_color_used_blue[i][j + 3] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_red[i][j + 3] = 0;
			english_text_color_used_green[i][j] = 255;
			english_text_color_used_green[i][j + 1] = 255;
			english_text_color_used_green[i][j + 2] = 255;
			english_text_color_used_green[i][j + 3] = 255;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			english_text_color_used_blue[i][j + 3] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_red[i][j + 3] = 0;
			english_barrier_color_used_green[i][j] = 255;
			english_barrier_color_used_green[i][j + 1] = 255;
			english_barrier_color_used_green[i][j + 2] = 255;
			english_barrier_color_used_green[i][j + 3] = 255;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			english_barrier_color_used_blue[i][j + 3] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = false;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
			english_top_barrier[i][j + 3] = true;
			english_right_barrier[i][j + 3] = true;
			english_bottom_barrier[i][j + 3] = true;
			english_left_barrier[i][j + 3] = false;
		} else if (english_original_positions[i][j] == "D" && english_original_positions[i][j + 1] == "r" && english_original_positions[i][j + 2] == "o" && english_original_positions[i][j + 3] == "w" && english_original_positions[i][j + 4] == "n") {
			english_code_used[i][j] = 3;
			english_code_used[i][j + 1] = 3;
			english_code_used[i][j + 2] = 3;
			english_code_used[i][j + 3] = 3;
			english_code_used[i][j + 4] = 3;
			
			if (english_code_used[i - 1][j] == 3) {
				english_background_color_used_red[i][j] = 0;
				english_background_color_used_red[i][j + 1] = 0;
				english_background_color_used_red[i][j + 2] = 0;
				english_background_color_used_red[i][j + 3] = 0;
				english_background_color_used_red[i][j + 4] = 0;
			} else { // Waves
				english_background_color_used_red[i][j] = -1;
				english_background_color_used_red[i][j + 1] = -1;
				english_background_color_used_red[i][j + 2] = -1;
				english_background_color_used_red[i][j + 3] = -1;
				english_background_color_used_red[i][j + 4] = -1;
			}
			english_background_color_used_green[i][j] = 0;
			english_background_color_used_green[i][j + 1] = 0;
			english_background_color_used_green[i][j + 2] = 0;
			english_background_color_used_green[i][j + 3] = 0;
			english_background_color_used_green[i][j + 4] = 0;
			english_background_color_used_blue[i][j] = 255;
			english_background_color_used_blue[i][j + 1] = 255;
			english_background_color_used_blue[i][j + 2] = 255;
			english_background_color_used_blue[i][j + 3] = 255;
			english_background_color_used_blue[i][j + 4] = 255;
			
			english_text_color_used_red[i][j] = 0;
			english_text_color_used_red[i][j + 1] = 0;
			english_text_color_used_red[i][j + 2] = 0;
			english_text_color_used_red[i][j + 3] = 0;
			english_text_color_used_red[i][j + 4] = 0;
			english_text_color_used_green[i][j] = 255;
			english_text_color_used_green[i][j + 1] = 255;
			english_text_color_used_green[i][j + 2] = 255;
			english_text_color_used_green[i][j + 3] = 255;
			english_text_color_used_green[i][j + 4] = 255;
			english_text_color_used_blue[i][j] = 255;
			english_text_color_used_blue[i][j + 1] = 255;
			english_text_color_used_blue[i][j + 2] = 255;
			english_text_color_used_blue[i][j + 3] = 255;
			english_text_color_used_blue[i][j + 4] = 255;
			
			english_barrier_color_used_red[i][j] = 0;
			english_barrier_color_used_red[i][j + 1] = 0;
			english_barrier_color_used_red[i][j + 2] = 0;
			english_barrier_color_used_red[i][j + 3] = 0;
			english_barrier_color_used_red[i][j + 4] = 0;
			english_barrier_color_used_green[i][j] = 255;
			english_barrier_color_used_green[i][j + 1] = 255;
			english_barrier_color_used_green[i][j + 2] = 255;
			english_barrier_color_used_green[i][j + 3] = 255;
			english_barrier_color_used_green[i][j + 4] = 255;
			english_barrier_color_used_blue[i][j] = 255;
			english_barrier_color_used_blue[i][j + 1] = 255;
			english_barrier_color_used_blue[i][j + 2] = 255;
			english_barrier_color_used_blue[i][j + 3] = 255;
			english_barrier_color_used_blue[i][j + 4] = 255;
			
			english_top_barrier[i][j] = true;
			english_right_barrier[i][j] = false;
			english_bottom_barrier[i][j] = true;
			english_left_barrier[i][j] = true;
			english_top_barrier[i][j + 1] = true;
			english_right_barrier[i][j + 1] = false;
			english_bottom_barrier[i][j + 1] = true;
			english_left_barrier[i][j + 1] = false;
			english_top_barrier[i][j + 2] = true;
			english_right_barrier[i][j + 2] = false;
			english_bottom_barrier[i][j + 2] = true;
			english_left_barrier[i][j + 2] = false;
			english_top_barrier[i][j + 3] = true;
			english_right_barrier[i][j + 3] = false;
			english_bottom_barrier[i][j + 3] = true;
			english_left_barrier[i][j + 3] = false;
			english_top_barrier[i][j + 4] = true;
			english_right_barrier[i][j + 4] = true;
			english_bottom_barrier[i][j + 4] = true;
			english_left_barrier[i][j + 4] = false;
		}
	}
}


hero_name = "Daniel Meilland ";
hero_letter_change = 350;
hero_text_color = 255255128;
hero_background_color = 175075050;

hero_name_in_pieces = hero_name.split("");
temp = hero_text_color % 1000000;
hero_text_color_red = (hero_text_color - temp) / 1000000;
hero_text_color_blue = temp % 1000;
hero_text_color_green = (temp - hero_text_color_blue) / 1000;
temp = hero_background_color % 1000000;
hero_background_color_red = (hero_background_color - temp) / 1000000;
hero_background_color_blue = temp % 1000;
hero_background_color_green = (temp - hero_background_color_blue) / 1000;




miliseconds = 0;

// Play the music
music_peace = document.getElementById("music_peace");
music_peace.setAttribute("preload", "auto");
music_peace.autobuffer = true;    
music_peace.load();
music_peace.volume = 0.1;
//music_peace.play();

music_menu = document.getElementById("music_menu");
music_menu.setAttribute("preload", "auto");
music_menu.autobuffer = true;    
music_menu.load();
music_menu.volume = 0.1;
music_menu.play();

// Learned and learning languages for the main menu
main_menu = true;

learned_languages = new Array("English", "toki pona", "zjlimpa", "Türkçe", "Polski", "日本語", "ᏣᎳᎩ", "Esperanto", "Magyar", "中文/汉语/普通话", "Computer", "Woman", "Man", "Anarcho-capitalism", "$", "3dtest");
learning_languages = new Array("English", "Français", "Български");
english_speakers = new Array("daniel_meilland");
zjlimpa_speakers = new Array("sanjr_mjǎlethis");
toki_pona_speakers = new Array("jan_semansi");

language_spoken = -1;

learned_language = 0;
if (navigator.language.substr(0,2) == "en") {
learning_language = 0;
learned_language = 1;
} else if (navigator.language.substr(0,2) == "bg") {
learning_language = 2;
} else {
learning_language = 1;
}

language_description = [];

language_description["English"] = [];
language_description["English"]["English"] = [];
language_description["English"]["English"][0] = "English is the language of 54% of the websites (Press 1 to view source)";
language_description["English"]["English"][1] = "and the language of 7 of the 10 freest countries (Press 2 to view source):";
language_description["English"]["English"][2] = "Hong Kong, Singapore, New Zealand (also the least corrupt in 2017, ";
language_description["English"]["English"][3] = "press 3 to view source), Australia, Ireland, the United Kingdom and Canada.";

language_description["English"]["Français"] = [];
language_description["English"]["Français"][0] = "L'anglais est la langue de 54% des sites Web (Appuyez 1 pour voir la source)";
language_description["English"]["Français"][1] = "et la langue de 7 des 10 pays les plus libres (Appuyez 2 pour voir la source):";
language_description["English"]["Français"][2] = "Hong Kong, Singapour, la Nouvelle-Zélande (aussi le moins corrompu en 2017, ";
language_description["English"]["Français"][3] = "appuyez sur 3 pour voir la source), l'Australie, l'Irlande, le Royaume-Uni";
language_description["English"]["Français"][4] = "et le Canada.";

language_description["English"]["Български"] = [];
language_description["English"]["Български"][0] = "Английски е езика на 54% от уебсайтовете (Натиснете 1 за да видете източника)";
language_description["English"]["Български"][1] = "и на 7 от 10 най-свободни държави (Натиснете 2 за да видете източника):";
language_description["English"]["Български"][2] = "Хонг Конг, Сингапур, Нова Зеландия (също най-малко корумпираната в 2017 г., ";
language_description["English"]["Български"][3] = "натиснете 3 за да видете източника), Австралия, Ирландия, Обединено кралство";
language_description["English"]["Български"][4] = "и Канада.";

language_description["toki pona"] = [];
language_description["toki pona"]["toki pona"] = [];
language_description["toki pona"]["toki pona"][0] = "toki pona li toki pi jan Sonja. ona toki li lape pona! o jaki e ona!";

language_description["toki pona"]["English"] = [];
language_description["toki pona"]["English"][0] = "Toki Pona is the language of Sonja Lang. It is sleeping easy! Try it!";

language_description["toki pona"]["Français"] = [];
language_description["toki pona"]["Français"][0] = "Le Toki Pona est la langue de Sonja Lang. Cette langue est simple à en";
language_description["toki pona"]["Français"][1] = "dormir! Essayez-la!";

language_description["toki pona"]["Български"] = [];
language_description["toki pona"]["Български"][0] = "Токи Пона е езика на Соня Ланг. Е толкова просто че сте заспите!";
language_description["toki pona"]["Български"][1] = "Изпитнете го!";

language_description["zjlimpa"] = [];
language_description["zjlimpa"]["zjlimpa"] = [];
language_description["zjlimpa"]["zjlimpa"][0] = "Xi INS Phinaa pikpis j zjlimpa'e xii limpa";

language_description["zjlimpa"]["English"] = [];
language_description["zjlimpa"]["English"][0] = "I am the big boss of INS Phina and Rimaian is my language";

language_description["zjlimpa"]["Français"] = [];
language_description["zjlimpa"]["Français"][0] = "Je suis le patron d'INS Phina et le rimaïen est ma langue";

language_description["zjlimpa"]["Български"] = [];
language_description["zjlimpa"]["Български"][0] = "Аз съм шефа на ИНС Фина и римайски е моя език";

language_description["Türkçe"] = [];
language_description["Türkçe"]["Türkçe"] = [];
language_description["Türkçe"]["Türkçe"][0] = "Türkçeyi öğren! O döner kebabın, lokumun ve yoğurtun dilidir";

language_description["Türkçe"]["English"] = [];
language_description["Türkçe"]["English"][0] = "Learn Turkish! It is the language of doner kebab, Turkish delight and yoghurt";

language_description["Türkçe"]["Français"] = [];
language_description["Türkçe"]["Français"][0] = "Apprends le turc! C'est la langue des kebabs et des loukoums";

language_description["Türkçe"]["Български"] = [];
language_description["Türkçe"]["Български"][0] = "Научавай турски! То е езика на дюнерите, локумите и киселото мляко";

language_description["Polski"] = [];
language_description["Polski"]["Polski"] = [];
language_description["Polski"]["Polski"][0] = "Polski to język mężczyzn i kobiet";

language_description["Polski"]["English"] = [];
language_description["Polski"]["English"][0] = "Polish is the language of men and women";

language_description["Polski"]["Français"] = [];
language_description["Polski"]["Français"][0] = "Le polonais est la langue des hommes et des femmes";

language_description["Polski"]["Български"] = [];
language_description["Polski"]["Български"][0] = "Полски е езика на мъжете и жените";

language_description["日本語"] = [];
language_description["日本語"]["日本語"] = [];

language_description["日本語"]["English"] = [];

language_description["日本語"]["Français"] = [];

language_description["日本語"]["Български"] = [];

language_description["ᏣᎳᎩ"] = [];
language_description["ᏣᎳᎩ"]["ᏣᎳᎩ"] = [];

language_description["ᏣᎳᎩ"]["English"] = [];

language_description["ᏣᎳᎩ"]["Français"] = [];

language_description["ᏣᎳᎩ"]["Български"] = [];

language_description["Esperanto"] = [];
language_description["Esperanto"]["Esperanto"] = [];

language_description["Esperanto"]["English"] = [];

language_description["Esperanto"]["Français"] = [];

language_description["Esperanto"]["Български"] = [];

language_description["Magyar"] = [];
language_description["Magyar"]["Magyar"] = [];

language_description["Magyar"]["English"] = [];

language_description["Magyar"]["Français"] = [];

language_description["Magyar"]["Български"] = [];

language_description["中文/汉语/普通话"] = [];
language_description["中文/汉语/普通话"]["中文/汉语/普通话"] = [];

language_description["中文/汉语/普通话"]["English"] = [];

language_description["中文/汉语/普通话"]["Français"] = [];

language_description["中文/汉语/普通话"]["Български"] = [];

language_description["Computer"] = [];
language_description["Computer"]["Computer"] = [];

language_description["Computer"]["English"] = [];

language_description["Computer"]["Français"] = [];

language_description["Computer"]["Български"] = [];

language_description["Woman"] = [];
language_description["Woman"]["Woman"] = [];

language_description["Woman"]["English"] = [];

language_description["Woman"]["Français"] = [];

language_description["Woman"]["Български"] = [];

language_description["Man"] = [];
language_description["Man"]["Man"] = [];

language_description["Man"]["English"] = [];

language_description["Man"]["Français"] = [];

language_description["Man"]["Български"] = [];

language_description["Anarcho-capitalism"] = [];
language_description["Anarcho-capitalism"]["Anarcho-capitalism"] = [];

language_description["Anarcho-capitalism"]["English"] = [];

language_description["Anarcho-capitalism"]["Français"] = [];

language_description["Anarcho-capitalism"]["Български"] = [];

language_description["$"] = [];
language_description["$"]["$"] = [];

language_description["$"]["English"] = [];

language_description["$"]["Français"] = [];

language_description["$"]["Български"] = [];

language_description["3dtest"] = [];
language_description["3dtest"]["3dtest"] = [];

language_description["3dtest"]["English"] = [];

language_description["3dtest"]["Français"] = [];

language_description["3dtest"]["Български"] = [];






sound_to_play = document.getElementById("language_description_english_daniel_meilland");


dogline_this_is_a_test = [];

dogline_this_is_a_test["zjlimpa"] = "Hari'e mgǎztoct";
dogline_this_is_a_test["toki pona"] = "ni li lipu jaki";
dogline_this_is_a_test["English"] = "This is a test";
dogline_this_is_a_test["Français"] = "Ceci est un test";
dogline_this_is_a_test["Български"] = "Това е един тест";

dogline_10_s_lost = [];
dogline_10_s_lost["zjlimpa"] = "Ti rakit tii evtjǎǎ sgôôt'kjee kei";
dogline_10_s_lost["toki pona"] = "sina anpa e luka luka pilin pi pali sina";
dogline_10_s_lost["English"] = "You have lost ten seconds of your life";
dogline_10_s_lost["Français"] = "Vous avez perdu dix secondes de votre vie";
dogline_10_s_lost["Български"] = "Сте загубили десет секунди от Вашя живот";

dogline_1_min_lost = [];
dogline_1_min_lost["toki pona"] = "sina anpa e wan kon pi pali sina";
dogline_1_min_lost["English"] = "You have lost one minute of your life";
dogline_1_min_lost["Français"] = "Vous avez perdu une minute de votre vie";
dogline_1_min_lost["Български"] = "Сте загубили една минута от Вашя живот";

dogline_10_min_lost = [];
dogline_10_min_lost["toki pona"] = "sina anpa e luka luka kon pi pali sina";
dogline_10_min_lost["English"] = "You have lost ten minutes of your life";
dogline_10_min_lost["Français"] = "Vous avez perdu dix minutes de votre vie";
dogline_10_min_lost["Български"] = "Сте загубили десет минути от Вашя живот";

dogline_30_min_lost = [];
dogline_30_min_lost["toki pona"] = "sina anpa e mute luka luka kon pi pali sina";
dogline_30_min_lost["English"] = "You have lost half an hour of your life";
dogline_30_min_lost["Français"] = "Vous avez perdu une demi-heure de votre vie";
dogline_30_min_lost["Български"] = "Сте загубили един полвин час от Вашя живот";

dogline_1_h_lost = [];
dogline_1_h_lost["toki pona"] = "sina anpa e wan tawa pi pali sina";
dogline_1_h_lost["English"] = "You have lost an hour of your life";
dogline_1_h_lost["Français"] = "Vous avez perdu une heure de votre vie";
dogline_1_h_lost["Български"] = "Сте загубили един час от Вашя живот";

enter_world = [];
enter_world["toki pona"] = "o alasa e ala tawa tawa lon ma";
enter_world["English"] = "Press space to enter world";
enter_world["Français"] = "Appuyez sur espace pour entrer dans le monde";
enter_world["Български"] = "Натиснете интервал за да влезете в света";


dogline_message = new Array();
dogline_speed = new Array();
dogline_message[0] = "                                           ".split("");
dogline_speed[0] = 0;
dogline_message[1] = ("" + dogline_this_is_a_test[learned_languages[learned_language]] + " / " + dogline_this_is_a_test[learning_languages[learning_language]]).split("");
dogline_speed[1] = 25;
dogline_oldest_message = 0;
dogline_oldest_letter = 0;
dogline_oldest_subposition = 0; // From 0 to 99 
backspeed = false;

ajax_input = [];
ajax_output = "";
ajax_time = 0;

/*
activeline_10_start_x = 0;
activeline_10_start_y = 0;
activeline_10_end_x = 0;
activeline_10_end_y = 0;
activeline_10_red = 0;
activeline_10_green = 0;
activeline_10_blue = 0;

activeline_16_start_x = 0;
activeline_16_start_y = 0;
activeline_16_end_x = 0;
activeline_16_end_y = 0;
activeline_16_red = 0;
activeline_16_green = 0;
activeline_16_blue = 0;*/

storyofsounds_english_vowels = new Array("a", "a_e", "e", "e_e", "i", "i_e", "o", "o_e", "u", "u_e", "r", "ou");
storyofsounds_english_consonants = new Array("-", "m", "n", "b", "d", "j", "g", "sp", "st", "str", "sk", "p", "t", "ch", "k", "v", "f", "s", "sh", "h", "y", "l", "r", "w");

storyofsounds_english_vowel = 0;
storyofsounds_english_consonant = 0;
storyofsounds_english_view_vowel = 0;
storyofsounds_english_view_consonant = 0;

english_freshly_chosen = true;

storyofsounds_english_firsttableline = [];
storyofsounds_english_secondtableline = [];

storyofsounds_english_firstdownline = [];
storyofsounds_english_seconddownline = [];
storyofsounds_english_thirddownline = [];
storyofsounds_english_fourthdownline = [];

storyofsounds_english_firstdownline["Français"] = [];
storyofsounds_english_seconddownline["Français"] = [];
storyofsounds_english_thirddownline["Français"] = [];
storyofsounds_english_fourthdownline["Français"] = [];
storyofsounds_english_firstdownline["Български"] = [];
storyofsounds_english_seconddownline["Български"] = [];
storyofsounds_english_thirddownline["Български"] = [];
storyofsounds_english_fourthdownline["Български"] = [];
storyofsounds_english_firsttableline["Français"] = [];
storyofsounds_english_firsttableline["Български"] = [];
storyofsounds_english_secondtableline["Français"] = [];
storyofsounds_english_secondtableline["Български"] = [];


storyofsounds_english_firsttableline["Français"]["-a"] = "ass, at, axe";
storyofsounds_english_secondtableline["Français"]["-a"] = "cul, à";
storyofsounds_english_firstdownline["Français"]["-a"] = "ass: cul (USA. UK: arse), âne | at: indique l'heure, la destination, …";
storyofsounds_english_seconddownline["Français"]["-a"] = "axe: ";
storyofsounds_english_thirddownline["Français"]["-a"] = "";
storyofsounds_english_fourthdownline["Français"]["-a"] = "";

storyofsounds_english_firsttableline["Български"]["-a"] = "ass, at, axe";
storyofsounds_english_secondtableline["Български"]["-a"] = "дупе, на";
storyofsounds_english_firstdownline["Български"]["-a"] = "ass: дупе (САЩ. ОК: arse), магаре";
storyofsounds_english_seconddownline["Български"]["-a"] = "at: на, към, …";
storyofsounds_english_thirddownline["Български"]["-a"] = "";
storyofsounds_english_fourthdownline["Български"]["-a"] = "";


storyofsounds_english_firsttableline["Français"]["-a_e"] = "eight, ape";
storyofsounds_english_secondtableline["Français"]["-a_e"] = "8, ^singe";
storyofsounds_english_firstdownline["Français"]["-a_e"] = "eight: 8";
storyofsounds_english_seconddownline["Français"]["-a_e"] = "ape: grand singe";
storyofsounds_english_thirddownline["Français"]["-a_e"] = "";
storyofsounds_english_fourthdownline["Français"]["-a_e"] = "";

storyofsounds_english_firsttableline["Български"]["-a_e"] = "eight, A";
storyofsounds_english_secondtableline["Български"]["-a_e"] = "8, майму…";
storyofsounds_english_firstdownline["Български"]["-a_e"] = "eight: 8";
storyofsounds_english_seconddownline["Български"]["-a_e"] = "ape: голяма майнуна";
storyofsounds_english_thirddownline["Български"]["-a_e"] = "";
storyofsounds_english_fourthdownline["Български"]["-a_e"] = "";


storyofsounds_english_firsttableline["Français"]["-e"] = "F, L, M, N,";
storyofsounds_english_secondtableline["Français"]["-e"] = "S, X";
storyofsounds_english_firstdownline["Français"]["-e"] = "F, L, M, N, S, X: lettres de l'alphabet";
storyofsounds_english_seconddownline["Français"]["-e"] = "";
storyofsounds_english_thirddownline["Français"]["-e"] = "";
storyofsounds_english_fourthdownline["Français"]["-e"] = "";

storyofsounds_english_firsttableline["Български"]["-e"] = "F, L, M, N,";
storyofsounds_english_secondtableline["Български"]["-e"] = "S, X";
storyofsounds_english_firstdownline["Български"]["-e"] = "F, L, M, N, S, X: букви от азбуката";
storyofsounds_english_seconddownline["Български"]["-e"] = "";
storyofsounds_english_thirddownline["Български"]["-e"] = "";
storyofsounds_english_fourthdownline["Български"]["-e"] = "";


storyofsounds_english_firsttableline["Français"]["-e_e"] = "E, eat, easy";
storyofsounds_english_secondtableline["Français"]["-e_e"] = "manger, faci…";
storyofsounds_english_firstdownline["Français"]["-e_e"] = "eat: manger | easy: facile";
storyofsounds_english_seconddownline["Français"]["-e_e"] = "eel: anguille";
storyofsounds_english_thirddownline["Français"]["-e_e"] = "";
storyofsounds_english_fourthdownline["Français"]["-e_e"] = "";

storyofsounds_english_firsttableline["Български"]["-e_e"] = "E, eat";
storyofsounds_english_secondtableline["Български"]["-e_e"] = "дупе, на";
storyofsounds_english_firstdownline["Български"]["-e_e"] = "eat: ям | easy: лесно";
storyofsounds_english_seconddownline["Български"]["-e_e"] = "eel: змиорка";
storyofsounds_english_thirddownline["Български"]["-e_e"] = "";
storyofsounds_english_fourthdownline["Български"]["-e_e"] = "";


storyofsounds_english_firsttableline["Français"]["-i"] = "it, is";
storyofsounds_english_secondtableline["Français"]["-i"] = "il, est";
storyofsounds_english_firstdownline["Français"]["-i"] = "it: il (chose)";
storyofsounds_english_seconddownline["Français"]["-i"] = "";
storyofsounds_english_thirddownline["Français"]["-i"] = "";
storyofsounds_english_fourthdownline["Français"]["-i"] = "";

storyofsounds_english_firsttableline["Български"]["-i"] = "it, is";
storyofsounds_english_secondtableline["Български"]["-i"] = "той, е";
storyofsounds_english_firstdownline["Български"]["-i"] = "it: той (за неща)";
storyofsounds_english_seconddownline["Български"]["-i"] = "is: е (съм)";
storyofsounds_english_thirddownline["Български"]["-i"] = "";
storyofsounds_english_fourthdownline["Български"]["-i"] = "";


storyofsounds_english_firsttableline["Français"]["-i_e"] = "ice";
storyofsounds_english_secondtableline["Français"]["-i_e"] = "glace";
storyofsounds_english_firstdownline["Français"]["-i_e"] = "ice: glace (sur laquelle on glisse. Glace qu'on mange: ice cream)";
storyofsounds_english_seconddownline["Français"]["-i_e"] = "";
storyofsounds_english_thirddownline["Français"]["-i_e"] = "";
storyofsounds_english_fourthdownline["Français"]["-i_e"] = "";

storyofsounds_english_firsttableline["Български"]["-i_e"] = "ice";
storyofsounds_english_secondtableline["Български"]["-i_e"] = "лед";
storyofsounds_english_firstdownline["Български"]["-i_e"] = "ice: лед (сладолед: ice cream)";
storyofsounds_english_seconddownline["Български"]["-i_e"] = "";
storyofsounds_english_thirddownline["Български"]["-i_e"] = "";
storyofsounds_english_fourthdownline["Български"]["-i_e"] = "";


storyofsounds_english_firsttableline["Français"]["-o"] = "ox";
storyofsounds_english_secondtableline["Français"]["-o"] = "bœuf";
storyofsounds_english_firstdownline["Français"]["-o"] = "ox: bœuf";
storyofsounds_english_seconddownline["Français"]["-o"] = "";
storyofsounds_english_thirddownline["Français"]["-o"] = "";
storyofsounds_english_fourthdownline["Français"]["-o"] = "";

storyofsounds_english_firsttableline["Български"]["-o"] = "ox";
storyofsounds_english_secondtableline["Български"]["-o"] = "вол";
storyofsounds_english_firstdownline["Български"]["-o"] = "ox: вол";
storyofsounds_english_seconddownline["Български"]["-o"] = "";
storyofsounds_english_thirddownline["Български"]["-o"] = "";
storyofsounds_english_fourthdownline["Български"]["-o"] = "";


storyofsounds_english_firsttableline["Français"]["-o_e"] = "O, oath";
storyofsounds_english_secondtableline["Français"]["-o_e"] = "O, serment";
storyofsounds_english_firstdownline["Français"]["-o_e"] = "O (lettre de l'alphabet),";
storyofsounds_english_seconddownline["Français"]["-o_e"] = "oath: serment";
storyofsounds_english_thirddownline["Français"]["-o_e"] = "";
storyofsounds_english_fourthdownline["Français"]["-o_e"] = "";

storyofsounds_english_firsttableline["Български"]["-o_e"] = "O, oath";
storyofsounds_english_secondtableline["Български"]["-o_e"] = "O, заричане";
storyofsounds_english_firstdownline["Български"]["-o_e"] = "O (буква от азбуката),";
storyofsounds_english_seconddownline["Български"]["-o_e"] = "oath: заричане";
storyofsounds_english_thirddownline["Български"]["-o_e"] = "";
storyofsounds_english_fourthdownline["Български"]["-o_e"] = "";


storyofsounds_english_firsttableline["Français"]["-u"] = "us";
storyofsounds_english_secondtableline["Français"]["-u"] = "nous";
storyofsounds_english_firstdownline["Français"]["-u"] = "us: nous (accusatif)";
storyofsounds_english_seconddownline["Français"]["-u"] = "";
storyofsounds_english_thirddownline["Français"]["-u"] = "";
storyofsounds_english_fourthdownline["Français"]["-u"] = "";

storyofsounds_english_firsttableline["Български"]["-u"] = "us";
storyofsounds_english_secondtableline["Български"]["-u"] = "нас";
storyofsounds_english_firstdownline["Български"]["-u"] = "us: нас";
storyofsounds_english_seconddownline["Български"]["-u"] = "";
storyofsounds_english_thirddownline["Български"]["-u"] = "";
storyofsounds_english_fourthdownline["Български"]["-u"] = "";


storyofsounds_english_firsttableline["Français"]["-u_e"] = "youth";
storyofsounds_english_secondtableline["Français"]["-u_e"] = "jeunesse";
storyofsounds_english_firstdownline["Français"]["-u_e"] = "youth: jeunesse";
storyofsounds_english_seconddownline["Français"]["-u_e"] = "";
storyofsounds_english_thirddownline["Français"]["-u_e"] = "";
storyofsounds_english_fourthdownline["Français"]["-u_e"] = "";

storyofsounds_english_firsttableline["Български"]["-u_e"] = "youth";
storyofsounds_english_secondtableline["Български"]["-u_e"] = "младост";
storyofsounds_english_firstdownline["Български"]["-u_e"] = "youth: младост";
storyofsounds_english_seconddownline["Български"]["-u_e"] = "";
storyofsounds_english_thirddownline["Български"]["-u_e"] = "";
storyofsounds_english_fourthdownline["Български"]["-u_e"] = "";


storyofsounds_english_firsttableline["Français"]["-r"] = "Earth";
storyofsounds_english_secondtableline["Français"]["-r"] = "Terre";
storyofsounds_english_firstdownline["Français"]["-r"] = "Earth: Terre";
storyofsounds_english_seconddownline["Français"]["-r"] = "";
storyofsounds_english_thirddownline["Français"]["-r"] = "";
storyofsounds_english_fourthdownline["Français"]["-r"] = "";

storyofsounds_english_firsttableline["Български"]["-r"] = "Earth";
storyofsounds_english_secondtableline["Български"]["-r"] = "Земия";
storyofsounds_english_firstdownline["Български"]["-r"] = "Earth: Земия";
storyofsounds_english_seconddownline["Български"]["-r"] = "";
storyofsounds_english_thirddownline["Български"]["-r"] = "";
storyofsounds_english_fourthdownline["Български"]["-r"] = "";


storyofsounds_english_firsttableline["Français"]["-ou"] = "Ouch!";
storyofsounds_english_secondtableline["Français"]["-ou"] = "Aïe!";
storyofsounds_english_firstdownline["Français"]["-ou"] = "Ouch! : Aïe! (J'ai mal!)";
storyofsounds_english_seconddownline["Français"]["-ou"] = "out: en dehors";
storyofsounds_english_thirddownline["Français"]["-ou"] = "";
storyofsounds_english_fourthdownline["Français"]["-ou"] = "";

storyofsounds_english_firsttableline["Български"]["-ou"] = "Ouch!";
storyofsounds_english_secondtableline["Български"]["-ou"] = "Ох!";
storyofsounds_english_firstdownline["Български"]["-ou"] = "Ouch! : Ох! (Боли ме!)";
storyofsounds_english_seconddownline["Български"]["-ou"] = "out: вън";
storyofsounds_english_thirddownline["Български"]["-ou"] = "";
storyofsounds_english_fourthdownline["Български"]["-ou"] = "";



storyofsounds_english_firsttableline["Français"]["ma_e"] = "mate, make";
storyofsounds_english_secondtableline["Français"]["ma_e"] = "souris";
storyofsounds_english_firstdownline["Français"]["ma_e"] = "mouse: souris";
storyofsounds_english_seconddownline["Français"]["ma_e"] = "meow: miaou (cri du chat)";
storyofsounds_english_thirddownline["Français"]["ma_e"] = "";
storyofsounds_english_fourthdownline["Français"]["ma_e"] = "";

storyofsounds_english_firsttableline["Български"]["ma_e"] = "mouse";
storyofsounds_english_secondtableline["Български"]["ma_e"] = "мишка";
storyofsounds_english_firstdownline["Български"]["ma_e"] = "mouse: мишка";
storyofsounds_english_seconddownline["Български"]["ma_e"] = "meow: мяу (викането на котката)";
storyofsounds_english_thirddownline["Български"]["ma_e"] = "";
storyofsounds_english_fourthdownline["Български"]["ma_e"] = "";


storyofsounds_english_firsttableline["Français"]["mou"] = "mouse";
storyofsounds_english_secondtableline["Français"]["mou"] = "souris";
storyofsounds_english_firstdownline["Français"]["mou"] = "mouse: souris";
storyofsounds_english_seconddownline["Français"]["mou"] = "meow: miaou (cri du chat)";
storyofsounds_english_thirddownline["Français"]["mou"] = "";
storyofsounds_english_fourthdownline["Français"]["mou"] = "";

storyofsounds_english_firsttableline["Български"]["mou"] = "mouse";
storyofsounds_english_secondtableline["Български"]["mou"] = "мишка";
storyofsounds_english_firstdownline["Български"]["mou"] = "mouse: мишка";
storyofsounds_english_seconddownline["Български"]["mou"] = "meow: мяу (викането на котката)";
storyofsounds_english_thirddownline["Български"]["mou"] = "";
storyofsounds_english_fourthdownline["Български"]["mou"] = "";


storyofsounds_english_firsttableline["Français"]["mou"] = "mouse";
storyofsounds_english_secondtableline["Français"]["mou"] = "souris";
storyofsounds_english_firstdownline["Français"]["mou"] = "mouse: souris";
storyofsounds_english_seconddownline["Français"]["mou"] = "meow: miaou (cri du chat)";
storyofsounds_english_thirddownline["Français"]["mou"] = "";
storyofsounds_english_fourthdownline["Français"]["mou"] = "";

storyofsounds_english_firsttableline["Български"]["mou"] = "mouse";
storyofsounds_english_secondtableline["Български"]["mou"] = "мишка";
storyofsounds_english_firstdownline["Български"]["mou"] = "mouse: мишка";
storyofsounds_english_seconddownline["Български"]["mou"] = "meow: мяу (викането на котката)";
storyofsounds_english_thirddownline["Български"]["mou"] = "";
storyofsounds_english_fourthdownline["Български"]["mou"] = "";


storyofsounds_toki_pona_second_consonants = new Array("-", "m", "n", "p", "t", "k", "s", "w", "l", "j");
storyofsounds_toki_pona_first_consonants = new Array("-", "m", "n", "p", "t", "k", "s", "w", "l", "j");

storyofsounds_toki_pona_second_consonant = 0;
storyofsounds_toki_pona_first_consonant = 0;
storyofsounds_toki_pona_view_second_consonant = 0;
storyofsounds_toki_pona_view_first_consonant = 0;

toki_pona_freshly_chosen = true;

storyofsounds_toki_pona_firsttableline = [];
storyofsounds_toki_pona_secondtableline = [];

storyofsounds_toki_pona_firstdownline = [];
storyofsounds_toki_pona_seconddownline = [];
storyofsounds_toki_pona_thirddownline = [];
storyofsounds_toki_pona_fourthdownline = [];

storyofsounds_toki_pona_firstdownline["English"] = [];
storyofsounds_toki_pona_seconddownline["English"] = [];
storyofsounds_toki_pona_thirddownline["English"] = [];
storyofsounds_toki_pona_fourthdownline["English"] = [];
storyofsounds_toki_pona_firstdownline["Français"] = [];
storyofsounds_toki_pona_seconddownline["Français"] = [];
storyofsounds_toki_pona_thirddownline["Français"] = [];
storyofsounds_toki_pona_fourthdownline["Français"] = [];
storyofsounds_toki_pona_firstdownline["Български"] = [];
storyofsounds_toki_pona_seconddownline["Български"] = [];
storyofsounds_toki_pona_thirddownline["Български"] = [];
storyofsounds_toki_pona_fourthdownline["Български"] = [];
storyofsounds_toki_pona_firsttableline["English"] = [];
storyofsounds_toki_pona_firsttableline["Français"] = [];
storyofsounds_toki_pona_firsttableline["Български"] = [];
storyofsounds_toki_pona_secondtableline["English"] = [];
storyofsounds_toki_pona_secondtableline["Français"] = [];
storyofsounds_toki_pona_secondtableline["Български"] = [];


storyofsounds_toki_pona_firsttableline["English"]["--"] = "e, a, o";
storyofsounds_toki_pona_secondtableline["English"]["--"] = "accusative";
storyofsounds_toki_pona_firstdownline["English"]["--"] = "e: accusative/direct object marker, and another direct object";
storyofsounds_toki_pona_seconddownline["English"]["--"] = "a: very";
storyofsounds_toki_pona_thirddownline["English"]["--"] = "o: vocative/call, imperative/command";
storyofsounds_toki_pona_fourthdownline["English"]["--"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["--"] = "e, a, o";
storyofsounds_toki_pona_secondtableline["Français"]["--"] = "accusatif";
storyofsounds_toki_pona_firstdownline["Français"]["--"] = "e: accusatif/marqueur d'objet direct, et un autre objet direct";
storyofsounds_toki_pona_seconddownline["Français"]["--"] = "a: très";
storyofsounds_toki_pona_thirddownline["Français"]["--"] = "o: vocatif/appel, impératif/commande";
storyofsounds_toki_pona_fourthdownline["Français"]["--"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["--"] = "e, a, o";
storyofsounds_toki_pona_secondtableline["Български"]["--"] = "винителен";
storyofsounds_toki_pona_firstdownline["Български"]["--"] = "e: винителен, друг винителен";
storyofsounds_toki_pona_seconddownline["Български"]["--"] = "a: много + прилагателно";
storyofsounds_toki_pona_thirddownline["Български"]["--"] = "o: звателен/обаждане, команда";
storyofsounds_toki_pona_fourthdownline["Български"]["--"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-m"] = "ma Amelika";
storyofsounds_toki_pona_firsttableline["English"]["-m"] = "ma Amelika";
storyofsounds_toki_pona_firstdownline["English"]["-m"] = "ma Amelika: America (continent)";
storyofsounds_toki_pona_seconddownline["English"]["-m"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-m"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-m"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-m"] = "ma Amelika";
storyofsounds_toki_pona_secondtableline["Français"]["-m"] = "Amérique";
storyofsounds_toki_pona_firstdownline["Français"]["-m"] = "ma Amelika: Amérique (continent)";
storyofsounds_toki_pona_seconddownline["Français"]["-m"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-m"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-m"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-m"] = "ma Amelika";
storyofsounds_toki_pona_secondtableline["Български"]["-m"] = "Америка";
storyofsounds_toki_pona_firstdownline["Български"]["-m"] = "ma Amelika: Америка";
storyofsounds_toki_pona_seconddownline["Български"]["-m"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-m"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-m"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-n"] = "";
storyofsounds_toki_pona_firsttableline["English"]["-n"] = "";
storyofsounds_toki_pona_firstdownline["English"]["-n"] = "";
storyofsounds_toki_pona_seconddownline["English"]["-n"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-n"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-n"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-n"] = "";
storyofsounds_toki_pona_secondtableline["Français"]["-n"] = "";
storyofsounds_toki_pona_firstdownline["Français"]["-n"] = "";
storyofsounds_toki_pona_seconddownline["Français"]["-n"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-n"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-n"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-n"] = "";
storyofsounds_toki_pona_secondtableline["Български"]["-n"] = "";
storyofsounds_toki_pona_firstdownline["Български"]["-n"] = "";
storyofsounds_toki_pona_seconddownline["Български"]["-n"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-n"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-n"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-p"] = "anpa";
storyofsounds_toki_pona_secondtableline["English"]["-p"] = "under";
storyofsounds_toki_pona_firstdownline["English"]["-p"] = "anpa: under";
storyofsounds_toki_pona_seconddownline["English"]["-p"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-p"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-p"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-p"] = "anpa";
storyofsounds_toki_pona_secondtableline["Français"]["-p"] = "sous";
storyofsounds_toki_pona_firstdownline["Français"]["-p"] = "anpa: sous";
storyofsounds_toki_pona_seconddownline["Français"]["-p"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-p"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-p"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-p"] = "anpa";
storyofsounds_toki_pona_secondtableline["Български"]["-p"] = "под";
storyofsounds_toki_pona_firstdownline["Български"]["-p"] = "anpa: под";
storyofsounds_toki_pona_seconddownline["Български"]["-p"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-p"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-p"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-t"] = "ante";
storyofsounds_toki_pona_secondtableline["English"]["-t"] = "different";
storyofsounds_toki_pona_firstdownline["English"]["-t"] = "ante: different";
storyofsounds_toki_pona_seconddownline["English"]["-t"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-t"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-t"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-t"] = "ante";
storyofsounds_toki_pona_secondtableline["Français"]["-t"] = "différent";
storyofsounds_toki_pona_firstdownline["Français"]["-t"] = "ante: différent";
storyofsounds_toki_pona_seconddownline["Français"]["-t"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-t"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-t"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-t"] = "ante";
storyofsounds_toki_pona_secondtableline["Български"]["-t"] = "различно";
storyofsounds_toki_pona_firstdownline["Български"]["-t"] = "ante: различно";
storyofsounds_toki_pona_seconddownline["Български"]["-t"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-t"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-t"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-k"] = "akesi";
storyofsounds_toki_pona_secondtableline["English"]["-k"] = "reptile";
storyofsounds_toki_pona_firstdownline["English"]["-k"] = "akesi: reptile, amphibian, monster";
storyofsounds_toki_pona_seconddownline["English"]["-k"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-k"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-k"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-k"] = "akesi";
storyofsounds_toki_pona_secondtableline["Français"]["-k"] = "reptile";
storyofsounds_toki_pona_firstdownline["Français"]["-k"] = "akesi: reptile, amphibien, monstre";
storyofsounds_toki_pona_seconddownline["Français"]["-k"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-k"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-k"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-k"] = "akesi";
storyofsounds_toki_pona_secondtableline["Български"]["-k"] = "влечуго";
storyofsounds_toki_pona_firstdownline["Български"]["-k"] = "akesi: влечуго, земноводно животно, чудовище";
storyofsounds_toki_pona_seconddownline["Български"]["-k"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-k"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-k"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-s"] = "insa";
storyofsounds_toki_pona_secondtableline["English"]["-s"] = "inside";
storyofsounds_toki_pona_firstdownline["English"]["-s"] = "insa: inside";
storyofsounds_toki_pona_seconddownline["English"]["-s"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-s"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-s"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-s"] = "insa";
storyofsounds_toki_pona_secondtableline["Français"]["-s"] = "intérieur";
storyofsounds_toki_pona_firstdownline["Français"]["-s"] = "insa: intérieur";
storyofsounds_toki_pona_seconddownline["Français"]["-s"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-s"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-s"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-s"] = "insa";
storyofsounds_toki_pona_secondtableline["Български"]["-s"] = "вътре";
storyofsounds_toki_pona_firstdownline["Български"]["-s"] = "insa: вътре";
storyofsounds_toki_pona_seconddownline["Български"]["-s"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-s"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-s"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-w"] = "";
storyofsounds_toki_pona_secondtableline["English"]["-w"] = "";
storyofsounds_toki_pona_firstdownline["English"]["-w"] = "";
storyofsounds_toki_pona_seconddownline["English"]["-w"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-w"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-w"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-w"] = "";
storyofsounds_toki_pona_secondtableline["Français"]["-w"] = "";
storyofsounds_toki_pona_firstdownline["Français"]["-w"] = "";
storyofsounds_toki_pona_seconddownline["Français"]["-w"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-w"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-w"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-w"] = "";
storyofsounds_toki_pona_secondtableline["Български"]["-w"] = "";
storyofsounds_toki_pona_firstdownline["Български"]["-w"] = "";
storyofsounds_toki_pona_seconddownline["Български"]["-w"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-w"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-w"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-l"] = "ilo";
storyofsounds_toki_pona_secondtableline["English"]["-l"] = "tool";
storyofsounds_toki_pona_firstdownline["English"]["-l"] = "ilo: tool";
storyofsounds_toki_pona_seconddownline["English"]["-l"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-l"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-l"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-l"] = "ilo";
storyofsounds_toki_pona_secondtableline["Français"]["-l"] = "outil";
storyofsounds_toki_pona_firstdownline["Français"]["-l"] = "ilo: outil";
storyofsounds_toki_pona_seconddownline["Français"]["-l"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-l"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-l"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-l"] = "ilo";
storyofsounds_toki_pona_secondtableline["Български"]["-l"] = "инструмент";
storyofsounds_toki_pona_firstdownline["Български"]["-l"] = "ilo: инструмент";
storyofsounds_toki_pona_seconddownline["Български"]["-l"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-l"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-l"] = "";


storyofsounds_toki_pona_firsttableline["English"]["-j"] = "ijo";
storyofsounds_toki_pona_secondtableline["English"]["-j"] = "thing";
storyofsounds_toki_pona_firstdownline["English"]["-j"] = "ijo: thing";
storyofsounds_toki_pona_seconddownline["English"]["-j"] = "";
storyofsounds_toki_pona_thirddownline["English"]["-j"] = "";
storyofsounds_toki_pona_fourthdownline["English"]["-j"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["-j"] = "ijo";
storyofsounds_toki_pona_secondtableline["Français"]["-j"] = "chose";
storyofsounds_toki_pona_firstdownline["Français"]["-j"] = "ijo: chose";
storyofsounds_toki_pona_seconddownline["Français"]["-j"] = "";
storyofsounds_toki_pona_thirddownline["Français"]["-j"] = "";
storyofsounds_toki_pona_fourthdownline["Français"]["-j"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["-j"] = "ijo";
storyofsounds_toki_pona_secondtableline["Български"]["-j"] = "нещо";
storyofsounds_toki_pona_firstdownline["Български"]["-j"] = "ijo: нещо";
storyofsounds_toki_pona_seconddownline["Български"]["-j"] = "";
storyofsounds_toki_pona_thirddownline["Български"]["-j"] = "";
storyofsounds_toki_pona_fourthdownline["Български"]["-j"] = "";


storyofsounds_toki_pona_firsttableline["English"]["m-"] = "ma, mi";
storyofsounds_toki_pona_secondtableline["English"]["m-"] = "place, I";
storyofsounds_toki_pona_firstdownline["English"]["m-"] = "ma: place, country, continent";
storyofsounds_toki_pona_seconddownline["English"]["m-"] = "ma tomo: city";
storyofsounds_toki_pona_thirddownline["English"]["m-"] = "mi: me, I";
storyofsounds_toki_pona_fourthdownline["English"]["m-"] = "";

storyofsounds_toki_pona_firsttableline["Français"]["m-"] = "ma, mi";
storyofsounds_toki_pona_secondtableline["Français"]["m-"] = "place, moi";
storyofsounds_toki_pona_firstdownline["Français"]["m-"] = "ma: place, pays, continent";
storyofsounds_toki_pona_seconddownline["Français"]["m-"] = "ma tomo: ville";
storyofsounds_toki_pona_thirddownline["Français"]["m-"] = "mi: moi, je";
storyofsounds_toki_pona_fourthdownline["Français"]["m-"] = "";

storyofsounds_toki_pona_firsttableline["Български"]["m-"] = "ma, mi";
storyofsounds_toki_pona_secondtableline["Български"]["m-"] = "място, аз";
storyofsounds_toki_pona_firstdownline["Български"]["m-"] = "ma: място";
storyofsounds_toki_pona_seconddownline["Български"]["m-"] = "ma tomo: град";
storyofsounds_toki_pona_thirddownline["Български"]["m-"] = "mi: аз, мене";
storyofsounds_toki_pona_fourthdownline["Български"]["m-"] = "";




storyofsounds_zjlimpa_vowels = new Array("a", "e", "i", "o", "g", "r", "y", "q", "u", "b", "j", "w");
storyofsounds_zjlimpa_consonants = new Array("-", "d", "c", "v", "m", "n", "p", "t", "k", "z", "x", "f", "s", "h", "l");

storyofsounds_zjlimpa_vowel = 0;
storyofsounds_zjlimpa_consonant = 0;
storyofsounds_zjlimpa_view_vowel = 0;
storyofsounds_zjlimpa_view_consonant = 0;

zjlimpa_freshly_chosen = true;

storyofsounds_zjlimpa_firsttableline = [];
storyofsounds_zjlimpa_secondtableline = [];

storyofsounds_zjlimpa_firstdownline = [];
storyofsounds_zjlimpa_seconddownline = [];
storyofsounds_zjlimpa_thirddownline = [];
storyofsounds_zjlimpa_fourthdownline = [];

storyofsounds_zjlimpa_firstdownline["English"] = [];
storyofsounds_zjlimpa_seconddownline["English"] = [];
storyofsounds_zjlimpa_thirddownline["English"] = [];
storyofsounds_zjlimpa_fourthdownline["English"] = [];
storyofsounds_zjlimpa_firstdownline["Français"] = [];
storyofsounds_zjlimpa_seconddownline["Français"] = [];
storyofsounds_zjlimpa_thirddownline["Français"] = [];
storyofsounds_zjlimpa_fourthdownline["Français"] = [];
storyofsounds_zjlimpa_firstdownline["Български"] = [];
storyofsounds_zjlimpa_seconddownline["Български"] = [];
storyofsounds_zjlimpa_thirddownline["Български"] = [];
storyofsounds_zjlimpa_fourthdownline["Български"] = [];
storyofsounds_zjlimpa_firsttableline["English"] = [];
storyofsounds_zjlimpa_firsttableline["Français"] = [];
storyofsounds_zjlimpa_firsttableline["Български"] = [];
storyofsounds_zjlimpa_secondtableline["English"] = [];
storyofsounds_zjlimpa_secondtableline["Français"] = [];
storyofsounds_zjlimpa_secondtableline["Български"] = [];


storyofsounds_zjlimpa_firsttableline["English"]["-a"] = "wild, fruit,";
storyofsounds_zjlimpa_secondtableline["English"]["-a"] = "female";
storyofsounds_zjlimpa_firstdownline["English"]["-a"] = "wild (from the cry of the wolf minus the cry of the dog),";
storyofsounds_zjlimpa_seconddownline["English"]["-a"] = "fruit (form italian pera VS pero),";
storyofsounds_zjlimpa_thirddownline["English"]["-a"] = "female (form the Romance suffix)";
storyofsounds_zjlimpa_fourthdownline["English"]["-a"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-a"] = "sauvage,";
storyofsounds_zjlimpa_secondtableline["Français"]["-a"] = "fruit";
storyofsounds_zjlimpa_firstdownline["Français"]["-a"] = "sauvage (du cri du loup moins celui du chien),";
storyofsounds_zjlimpa_seconddownline["Français"]["-a"] = "fruit (de l'italien pera VS pero),";
storyofsounds_zjlimpa_thirddownline["Français"]["-a"] = "femelle (du suffixe des langues romanes)";
storyofsounds_zjlimpa_fourthdownline["Français"]["-a"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-a"] = "див, плод,";
storyofsounds_zjlimpa_secondtableline["Български"]["-a"] = "женско";
storyofsounds_zjlimpa_firstdownline["Български"]["-a"] = "див (от викането на вълка минус този от кучето),";
storyofsounds_zjlimpa_seconddownline["Български"]["-a"] = "плод (от италиански pera срещу pero),";
storyofsounds_zjlimpa_thirddownline["Български"]["-a"] = "женско (от наставката от римските езици)";
storyofsounds_zjlimpa_fourthdownline["Български"]["-a"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-e"] = "alive,";
storyofsounds_zjlimpa_secondtableline["English"]["-e"] = "one unit";
storyofsounds_zjlimpa_firstdownline["English"]["-e"] = "prefix for living animals, life (native Rarimaish word),";
storyofsounds_zjlimpa_seconddownline["English"]["-e"] = "one unit, one entity (always followed by -i or -po; native Rarimaish word)";
storyofsounds_zjlimpa_thirddownline["English"]["-e"] = "fruits (Italian pere)";
storyofsounds_zjlimpa_fourthdownline["English"]["-e"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-e"] = "vivant,";
storyofsounds_zjlimpa_secondtableline["Français"]["-e"] = "une unité";
storyofsounds_zjlimpa_firstdownline["Français"]["-e"] = "préfixe pour les animaux vivants, vie (mot rimaïen)";
storyofsounds_zjlimpa_seconddownline["Français"]["-e"] = "une unité, une entité (toujours suivi de -i ou -po; mot rimaïen)";
storyofsounds_zjlimpa_thirddownline["Français"]["-e"] = "fruits (italien pere)";
storyofsounds_zjlimpa_fourthdownline["Français"]["-e"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-e"] = "жив, една";
storyofsounds_zjlimpa_secondtableline["Български"]["-e"] = "единица";
storyofsounds_zjlimpa_firstdownline["Български"]["-e"] = "префикс за живи животрни (раримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["-e"] = "една единица (винаги следвено от -i или -po; раримайска дима)";
storyofsounds_zjlimpa_thirddownline["Български"]["-e"] = "плодове (италиански pere)";
storyofsounds_zjlimpa_fourthdownline["Български"]["-e"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-i"] = "yin, ing,";
storyofsounds_zjlimpa_secondtableline["English"]["-i"] = "respect";
storyofsounds_zjlimpa_firstdownline["English"]["-i"] = "ik: yin (from Chinese 阴),";
storyofsounds_zjlimpa_seconddownline["English"]["-i"] = "ik: -ing (from English -ing), trees (from Italian peri)";
storyofsounds_zjlimpa_thirddownline["English"]["-i"] = "respect (suffix; native Rarimaish word),";
storyofsounds_zjlimpa_fourthdownline["English"]["-i"] = "language (from Chinese 语)";

storyofsounds_zjlimpa_firsttableline["Français"]["-i"] = "yin,";
storyofsounds_zjlimpa_secondtableline["Français"]["-i"] = "ing";
storyofsounds_zjlimpa_firstdownline["Français"]["-i"] = "ik: yin (du chinois 阴), arbres (de l'italien peri)";
storyofsounds_zjlimpa_seconddownline["Français"]["-i"] = "ik: -ing, en train de (de l'anglais -ing),";
storyofsounds_zjlimpa_thirddownline["Français"]["-i"] = "respect (sufixe; mot rimaïen)";
storyofsounds_zjlimpa_fourthdownline["Français"]["-i"] = "langue (du chinois 语)";

storyofsounds_zjlimpa_firsttableline["Български"]["-i"] = "ин, инг";
storyofsounds_zjlimpa_secondtableline["Български"]["-i"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["-i"] = "ik: ин (от китайски 阴), дърва (от италиански peri)";
storyofsounds_zjlimpa_seconddownline["Български"]["-i"] = "ik: инг (от английски -ing)";
storyofsounds_zjlimpa_thirddownline["Български"]["-i"] = "уважаван (наставка; раримайска дима)";
storyofsounds_zjlimpa_fourthdownline["Български"]["-i"] = "език (от китайски 语)";


storyofsounds_zjlimpa_firsttableline["English"]["-o"] = "tree,";
storyofsounds_zjlimpa_secondtableline["English"]["-o"] = "male";
storyofsounds_zjlimpa_firstdownline["English"]["-o"] = "tree (from Italian pero VS pera),";
storyofsounds_zjlimpa_seconddownline["English"]["-o"] = "male (from the Romance suffix),";
storyofsounds_zjlimpa_thirddownline["English"]["-o"] = "informal call, Oh!, Eh! (from Toki Pona o)";
storyofsounds_zjlimpa_fourthdownline["English"]["-o"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-o"] = "arbre,";
storyofsounds_zjlimpa_secondtableline["Français"]["-o"] = "mâle";
storyofsounds_zjlimpa_firstdownline["Français"]["-o"] = "arbre (de l'italien pero VS pera),";
storyofsounds_zjlimpa_seconddownline["Français"]["-o"] = "mâle (du suffixe des langues romanes),";
storyofsounds_zjlimpa_thirddownline["Français"]["-o"] = "appel informel, Oh!, Eh! (du toki pona o)";
storyofsounds_zjlimpa_fourthdownline["Français"]["-o"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-o"] = "дърво,";
storyofsounds_zjlimpa_secondtableline["Български"]["-o"] = "мъжки";
storyofsounds_zjlimpa_firstdownline["Български"]["-o"] = "дърво (от италиански pero срещу pera),";
storyofsounds_zjlimpa_seconddownline["Български"]["-o"] = "мъжки (от латинската наставка)";
storyofsounds_zjlimpa_thirddownline["Български"]["-o"] = "неформално обаждане, Хей (от токи пона o)";
storyofsounds_zjlimpa_fourthdownline["Български"]["-o"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-g"] = "dead";
storyofsounds_zjlimpa_secondtableline["English"]["-g"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["-g"] = "dead (Rarimaish word)";
storyofsounds_zjlimpa_seconddownline["English"]["-g"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["-g"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-g"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-g"] = "mort";
storyofsounds_zjlimpa_secondtableline["Français"]["-g"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["-g"] = "mort (mot rimaïen)";
storyofsounds_zjlimpa_seconddownline["Français"]["-g"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["-g"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-g"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-g"] = "мъртъв";
storyofsounds_zjlimpa_secondtableline["Български"]["-g"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["-g"] = "мъртъв (раримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["-g"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["-g"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-g"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-r"] = "air, -er";
storyofsounds_zjlimpa_secondtableline["English"]["-r"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["-r"] = "air (from English air),";
storyofsounds_zjlimpa_seconddownline["English"]["-r"] = "-er (from English -er)";
storyofsounds_zjlimpa_thirddownline["English"]["-r"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-r"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-r"] = "air, -eur";
storyofsounds_zjlimpa_secondtableline["Français"]["-r"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["-r"] = "air (de l'anglais air),";
storyofsounds_zjlimpa_seconddownline["Français"]["-r"] = "-eur (de l'anglais -er)";
storyofsounds_zjlimpa_thirddownline["Français"]["-r"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-r"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-r"] = "въздух,";
storyofsounds_zjlimpa_secondtableline["Български"]["-r"] = "-ер";
storyofsounds_zjlimpa_firstdownline["Български"]["-r"] = "въздух (от английски air)";
storyofsounds_zjlimpa_seconddownline["Български"]["-r"] = "-ер (от английски -er)";
storyofsounds_zjlimpa_thirddownline["Български"]["-r"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-r"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-y"] = "woman";
storyofsounds_zjlimpa_secondtableline["English"]["-y"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["-y"] = "woman (native Rarimaish word)";
storyofsounds_zjlimpa_seconddownline["English"]["-y"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["-y"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-y"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-y"] = "femme";
storyofsounds_zjlimpa_secondtableline["Français"]["-y"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["-y"] = "femme (épouse: lele; mot rimaïen)";
storyofsounds_zjlimpa_seconddownline["Français"]["-y"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["-y"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-y"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-y"] = "жена";
storyofsounds_zjlimpa_secondtableline["Български"]["-y"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["-y"] = "жена (съпруга: lele; рараримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["-y"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["-y"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-y"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-q"] = "wheat,";
storyofsounds_zjlimpa_secondtableline["English"]["-q"] = "anti";
storyofsounds_zjlimpa_firstdownline["English"]["-q"] = "qk: wheat, yellow, (qfrta) blond, vagina (from English wheat),";
storyofsounds_zjlimpa_seconddownline["English"]["-q"] = "anti (from French anti)";
storyofsounds_zjlimpa_thirddownline["English"]["-q"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-q"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-q"] = "blé, jaune,";
storyofsounds_zjlimpa_secondtableline["Français"]["-q"] = "anti";
storyofsounds_zjlimpa_firstdownline["Français"]["-q"] = "qk: blé, jaune, (qfrta) blond, vagin (de l'anglais wheat),";
storyofsounds_zjlimpa_seconddownline["Français"]["-q"] = "anti (du français anti)";
storyofsounds_zjlimpa_thirddownline["Français"]["-q"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-q"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-q"] = "жито, жълто,";
storyofsounds_zjlimpa_secondtableline["Български"]["-q"] = "против";
storyofsounds_zjlimpa_firstdownline["Български"]["-q"] = "qk: жито, жълто, вагина (от английски wheat),";
storyofsounds_zjlimpa_seconddownline["Български"]["-q"] = "против (от френски anti)";
storyofsounds_zjlimpa_thirddownline["Български"]["-q"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-q"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-u"] = "man, wolf,";
storyofsounds_zjlimpa_secondtableline["English"]["-u"] = "call for hug";
storyofsounds_zjlimpa_firstdownline["English"]["-u"] = "man (native Rarimaish word),";
storyofsounds_zjlimpa_seconddownline["English"]["-u"] = "wolf, call for hug (hug: ou; from the cry of a wolf)";
storyofsounds_zjlimpa_thirddownline["English"]["-u"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-u"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-u"] = "homme,";
storyofsounds_zjlimpa_secondtableline["Français"]["-u"] = "loup";
storyofsounds_zjlimpa_firstdownline["Français"]["-u"] = "homme (mot rimaïen),";
storyofsounds_zjlimpa_seconddownline["Français"]["-u"] = "loup, appel pour câlin (câlin: ou; du cri du loup)";
storyofsounds_zjlimpa_thirddownline["Français"]["-u"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-u"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-u"] = "мъж,";
storyofsounds_zjlimpa_secondtableline["Български"]["-u"] = "вълк";
storyofsounds_zjlimpa_firstdownline["Български"]["-u"] = "мъж (раримайска дума),";
storyofsounds_zjlimpa_seconddownline["Български"]["-u"] = "вълк, обаждане за прегръщане (прегръщане: ou; от викането на вълка)";
storyofsounds_zjlimpa_thirddownline["Български"]["-u"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-u"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-b"] = "recipro-";
storyofsounds_zjlimpa_secondtableline["English"]["-b"] = "cally";
storyofsounds_zjlimpa_firstdownline["English"]["-b"] = "reciprocally (Lykkete ma Kytg b → Andrew and Kate love each other; Rarimaish),";
storyofsounds_zjlimpa_seconddownline["English"]["-b"] = "baby (from the cry of a chick)";
storyofsounds_zjlimpa_thirddownline["English"]["-b"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-b"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-b"] = "récipro-";
storyofsounds_zjlimpa_secondtableline["Français"]["-b"] = "quement";
storyofsounds_zjlimpa_firstdownline["Français"]["-b"] = "réciproquement (Lykkete ma Kytg b → André et Catherine s'aiment; rimaïen),";
storyofsounds_zjlimpa_seconddownline["Français"]["-b"] = "bébé (du cri d'un poussin)";
storyofsounds_zjlimpa_thirddownline["Français"]["-b"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-b"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-b"] = "реципрочно";
storyofsounds_zjlimpa_secondtableline["Български"]["-b"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["-b"] = "реципрочно (Lykkete ma Kytg b → Райе и Кайдон се обичат; раримайски),";
storyofsounds_zjlimpa_seconddownline["Български"]["-b"] = "бебе (от викането на пиленцето)";
storyofsounds_zjlimpa_thirddownline["Български"]["-b"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-b"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-j"] = "yang, and,";
storyofsounds_zjlimpa_secondtableline["English"]["-j"] = "grandfather";
storyofsounds_zjlimpa_firstdownline["English"]["-j"] = "jk: yang (from Chinese 阳),";
storyofsounds_zjlimpa_seconddownline["English"]["-j"] = "and (from Bulgarian и),";
storyofsounds_zjlimpa_thirddownline["English"]["-j"] = "paternal grandfather (from Chinese 爷)";
storyofsounds_zjlimpa_fourthdownline["English"]["-j"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-j"] = "yang, et,";
storyofsounds_zjlimpa_secondtableline["Français"]["-j"] = "grand-père";
storyofsounds_zjlimpa_firstdownline["Français"]["-j"] = "jk: yang (du chinois 阳),";
storyofsounds_zjlimpa_seconddownline["Français"]["-j"] = "et (du bulgare и),";
storyofsounds_zjlimpa_thirddownline["Français"]["-j"] = "grand-père paternel (du chinois 爷)";
storyofsounds_zjlimpa_fourthdownline["Français"]["-j"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-j"] = "ян, и,";
storyofsounds_zjlimpa_secondtableline["Български"]["-j"] = "дядо";
storyofsounds_zjlimpa_firstdownline["Български"]["-j"] = "jk: ян (от китайски 阳)";
storyofsounds_zjlimpa_seconddownline["Български"]["-j"] = "и (от български и)";
storyofsounds_zjlimpa_thirddownline["Български"]["-j"] = "дядо (от китайски 爷)";
storyofsounds_zjlimpa_fourthdownline["Български"]["-j"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["-w"] = "or, dog";
storyofsounds_zjlimpa_secondtableline["English"]["-w"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["-w"] = "or (from French ou),";
storyofsounds_zjlimpa_seconddownline["English"]["-w"] = "dog, (Xiw: Christian) follower (from the cry of a dog),";
storyofsounds_zjlimpa_thirddownline["English"]["-w"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["-w"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["-w"] = "ou, chien";
storyofsounds_zjlimpa_secondtableline["Français"]["-w"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["-w"] = "ou (du français ou),";
storyofsounds_zjlimpa_seconddownline["Français"]["-w"] = "chien, (Xiw: chrétien) fidèle (du cri d'un chien)";
storyofsounds_zjlimpa_thirddownline["Français"]["-w"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["-w"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["-w"] = "или, куче";
storyofsounds_zjlimpa_secondtableline["Български"]["-w"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["-w"] = "или (от френски ou)";
storyofsounds_zjlimpa_seconddownline["Български"]["-w"] = "куче, (Xiw: християн) последовател (от викането на кучето)";
storyofsounds_zjlimpa_thirddownline["Български"]["-w"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["-w"] = "";



storyofsounds_zjlimpa_firsttableline["English"]["de"] = "species";
storyofsounds_zjlimpa_secondtableline["English"]["de"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["de"] = "species, pejorative call (Hryde: you slut; from French espèce)";
storyofsounds_zjlimpa_seconddownline["English"]["de"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["de"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["de"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["de"] = "espèce (de)";
storyofsounds_zjlimpa_secondtableline["Français"]["de"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["de"] = "espèce, ";
storyofsounds_zjlimpa_seconddownline["Français"]["de"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["de"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["de"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["de"] = "обица";
storyofsounds_zjlimpa_secondtableline["Български"]["de"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["de"] = "обица (от кхоса icici)";
storyofsounds_zjlimpa_seconddownline["Български"]["de"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["de"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["de"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["di"] = "earring";
storyofsounds_zjlimpa_secondtableline["English"]["di"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["di"] = "earring (from Xhosa icici)";
storyofsounds_zjlimpa_seconddownline["English"]["di"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["di"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["di"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["di"] = "boucle";
storyofsounds_zjlimpa_secondtableline["Français"]["di"] = "d'oreille";
storyofsounds_zjlimpa_firstdownline["Français"]["di"] = "boucle d'oreille (du Xhosa icici)";
storyofsounds_zjlimpa_seconddownline["Français"]["di"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["di"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["di"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["di"] = "обица";
storyofsounds_zjlimpa_secondtableline["Български"]["di"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["di"] = "обица (от кхоса icici)";
storyofsounds_zjlimpa_seconddownline["Български"]["di"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["di"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["di"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["do"] = "time";
storyofsounds_zjlimpa_secondtableline["English"]["do"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["do"] = "time (from the sound of a clock)";
storyofsounds_zjlimpa_seconddownline["English"]["do"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["do"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["do"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["do"] = "temps";
storyofsounds_zjlimpa_secondtableline["Français"]["do"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["do"] = "temps (du son d'une horloge)";
storyofsounds_zjlimpa_seconddownline["Français"]["do"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["do"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["do"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["do"] = "време";
storyofsounds_zjlimpa_secondtableline["Български"]["do"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["do"] = "префикс за живи животрни (Рараримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["do"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["do"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["do"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["dr"] = "music";
storyofsounds_zjlimpa_secondtableline["English"]["dr"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["dr"] = "music (from Xhosa umculu)";
storyofsounds_zjlimpa_seconddownline["English"]["dr"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["dr"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["dr"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["dr"] = "musique";
storyofsounds_zjlimpa_secondtableline["Français"]["dr"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["dr"] = "musique (du xhosa umculu)";
storyofsounds_zjlimpa_seconddownline["Français"]["dr"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["dr"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["dr"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["dr"] = "музика";
storyofsounds_zjlimpa_secondtableline["Български"]["dr"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["dr"] = "музика (от кхоса umculu)";
storyofsounds_zjlimpa_seconddownline["Български"]["dr"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["dr"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["dr"] = "";



storyofsounds_zjlimpa_firsttableline["English"]["ci"] = "kind but";
storyofsounds_zjlimpa_secondtableline["English"]["ci"] = "pitiful";
storyofsounds_zjlimpa_firstdownline["English"]["ci"] = "kind but pitiful (from French gentil)";
storyofsounds_zjlimpa_seconddownline["English"]["ci"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["ci"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["ci"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["ci"] = "gentil mais";
storyofsounds_zjlimpa_secondtableline["Français"]["ci"] = "pitoyable";
storyofsounds_zjlimpa_firstdownline["Français"]["ci"] = "gentil mais pitoyable (du français gentil)";
storyofsounds_zjlimpa_seconddownline["Français"]["ci"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["ci"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["ci"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["ci"] = "хубаво,";
storyofsounds_zjlimpa_secondtableline["Български"]["ci"] = "но жалко";
storyofsounds_zjlimpa_firstdownline["Български"]["ci"] = "хубаво, но жалко (от френски gentil)";
storyofsounds_zjlimpa_seconddownline["Български"]["ci"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["ci"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["ci"] = "";



storyofsounds_zjlimpa_firsttableline["English"]["ma"] = "mother,";
storyofsounds_zjlimpa_secondtableline["English"]["ma"] = "love";
storyofsounds_zjlimpa_firstdownline["English"]["ma"] = "mother (from Chinese 妈),";
storyofsounds_zjlimpa_seconddownline["English"]["ma"] = "love (from the sound of a kiss)";
storyofsounds_zjlimpa_thirddownline["English"]["ma"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["ma"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["ma"] = "mère,";
storyofsounds_zjlimpa_secondtableline["Français"]["ma"] = "amour";
storyofsounds_zjlimpa_firstdownline["Français"]["ma"] = "mère (du chinois 妈)";
storyofsounds_zjlimpa_seconddownline["Français"]["ma"] = "amour (du son d'un bisou)";
storyofsounds_zjlimpa_thirddownline["Français"]["ma"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["ma"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["ma"] = "майка,";
storyofsounds_zjlimpa_secondtableline["Български"]["ma"] = "любов";
storyofsounds_zjlimpa_firstdownline["Български"]["ma"] = "майка (от китайски 妈),";
storyofsounds_zjlimpa_seconddownline["Български"]["ma"] = "любов (от звука на една целувка)";
storyofsounds_zjlimpa_thirddownline["Български"]["ma"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["ma"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["me"] = "sheep,";
storyofsounds_zjlimpa_secondtableline["English"]["me"] = "sister";
storyofsounds_zjlimpa_firstdownline["English"]["me"] = "sheep (from the cry of a sheep),";
storyofsounds_zjlimpa_seconddownline["English"]["me"] = "younger sister, female trainee, woman younger than";
storyofsounds_zjlimpa_thirddownline["English"]["me"] = "the speaker but older than their first child";
storyofsounds_zjlimpa_fourthdownline["English"]["me"] = "(from Chinese 妹), mel-: apple (Italian mela)";

storyofsounds_zjlimpa_firsttableline["Français"]["me"] = "mouton,";
storyofsounds_zjlimpa_secondtableline["Français"]["me"] = "sœur";
storyofsounds_zjlimpa_firstdownline["Français"]["me"] = "mouton (du cri du mouton),";
storyofsounds_zjlimpa_seconddownline["Français"]["me"] = "petite sœur, stagiaire femme, femme plus jeune";
storyofsounds_zjlimpa_thirddownline["Français"]["me"] = "que le locuteur mais plus âgé que son premier enfant";
storyofsounds_zjlimpa_fourthdownline["Français"]["me"] = "(du chinois 妹), mel-: pomme (italien mela)";

storyofsounds_zjlimpa_firsttableline["Български"]["me"] = "овца";
storyofsounds_zjlimpa_secondtableline["Български"]["me"] = "сестричка";
storyofsounds_zjlimpa_firstdownline["Български"]["me"] = "овца (от викането на овцата),";
storyofsounds_zjlimpa_seconddownline["Български"]["me"] = "малка сестра, стажантка, жена по млада от";
storyofsounds_zjlimpa_thirddownline["Български"]["me"] = "този който говори но по възрастна от колквото свойто";
storyofsounds_zjlimpa_fourthdownline["Български"]["me"] = "първо дете (от китайски 妹), mel-: ябълка (италиански mela)";


storyofsounds_zjlimpa_firsttableline["English"]["mi"] = "me, meet";
storyofsounds_zjlimpa_secondtableline["English"]["mi"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["mi"] = "me (inclusive we: miha, exculsive we: mipg; from English me),";
storyofsounds_zjlimpa_seconddownline["English"]["mi"] = "meet (from English meet)";
storyofsounds_zjlimpa_thirddownline["English"]["mi"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["mi"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["mi"] = "moi,";
storyofsounds_zjlimpa_secondtableline["Français"]["mi"] = "rencontrer";
storyofsounds_zjlimpa_firstdownline["Français"]["mi"] = "moi (nous inclusif: miha, nous exclusif: mipg; )";
storyofsounds_zjlimpa_seconddownline["Français"]["mi"] = "petite sœur, stagiaire femme, femme plus jeune";
storyofsounds_zjlimpa_thirddownline["Français"]["mi"] = "que le locuteur mais plus âgé que son premier enfant";
storyofsounds_zjlimpa_fourthdownline["Français"]["mi"] = "(du chinois 妹)";

storyofsounds_zjlimpa_firsttableline["Български"]["mi"] = "овца";
storyofsounds_zjlimpa_secondtableline["Български"]["mi"] = "сестричка";
storyofsounds_zjlimpa_firstdownline["Български"]["mi"] = "овца (от викането на овцата),";
storyofsounds_zjlimpa_seconddownline["Български"]["mi"] = "малка сестра, стажантка, жена по млада от";
storyofsounds_zjlimpa_thirddownline["Български"]["mi"] = "този който говори но по възрастна от колквото свойто";
storyofsounds_zjlimpa_fourthdownline["Български"]["mi"] = "първо дете (от китайски 妹)";


storyofsounds_zjlimpa_firsttableline["English"]["mo"] = "cow, call";
storyofsounds_zjlimpa_secondtableline["English"]["mo"] = "for mother";
storyofsounds_zjlimpa_firstdownline["English"]["mo"] = "cow, Europe (from the cry of a cow),";
storyofsounds_zjlimpa_seconddownline["English"]["mo"] = "call for mother/matriarch (from Bulgarian мамо)";
storyofsounds_zjlimpa_thirddownline["English"]["mo"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["mo"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["mo"] = "vache, ";
storyofsounds_zjlimpa_secondtableline["Français"]["mo"] = "Mère!";
storyofsounds_zjlimpa_firstdownline["Français"]["mo"] = "vache, Europe (du cri d'une vache)";
storyofsounds_zjlimpa_seconddownline["Français"]["mo"] = "appel pour mère ou matriarche (du bulgare мамо)";
storyofsounds_zjlimpa_thirddownline["Français"]["mo"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["mo"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["mo"] = "крава,";
storyofsounds_zjlimpa_secondtableline["Български"]["mo"] = "Мамо!";
storyofsounds_zjlimpa_firstdownline["Български"]["mo"] = "крава, Европа (от викането на кравата)";
storyofsounds_zjlimpa_seconddownline["Български"]["mo"] = "обаждане за майка или матриарх (от български мамо)";
storyofsounds_zjlimpa_thirddownline["Български"]["mo"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["mo"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["ny"] = "straight";
storyofsounds_zjlimpa_secondtableline["English"]["ny"] = "male";
storyofsounds_zjlimpa_firstdownline["English"]["ny"] = "straight male (from English womanizer)";
storyofsounds_zjlimpa_seconddownline["English"]["ny"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["ny"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["ny"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["ny"] = "mâle";
storyofsounds_zjlimpa_secondtableline["Français"]["ny"] = "hétéro";
storyofsounds_zjlimpa_firstdownline["Français"]["ny"] = "mâle hétéro (de l'anglais womanizer)";
storyofsounds_zjlimpa_seconddownline["Français"]["ny"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["ny"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["ny"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["ny"] = "крава,";
storyofsounds_zjlimpa_secondtableline["Български"]["ny"] = "Мамо!";
storyofsounds_zjlimpa_firstdownline["Български"]["ny"] = "крава, Европа (от викането на кравата)";
storyofsounds_zjlimpa_seconddownline["Български"]["ny"] = "обаждане за майка или матриарх (от български мамо)";
storyofsounds_zjlimpa_thirddownline["Български"]["ny"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["ny"] = "";



storyofsounds_zjlimpa_firsttableline["English"]["pe"] = "bread";
storyofsounds_zjlimpa_secondtableline["English"]["pe"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["pe"] = "bread (English bread, French pain)";
storyofsounds_zjlimpa_seconddownline["English"]["pe"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["pe"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["pe"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["pe"] = "moi,";
storyofsounds_zjlimpa_secondtableline["Français"]["pe"] = "rencontrer";
storyofsounds_zjlimpa_firstdownline["Français"]["pe"] = "moi (nous inclusif: miha, nous exclusif: mipg; )";
storyofsounds_zjlimpa_seconddownline["Français"]["pe"] = "petite sœur, stagiaire femme, femme plus jeune";
storyofsounds_zjlimpa_thirddownline["Français"]["pe"] = "que le locuteur mais plus âgé que son premier enfant";
storyofsounds_zjlimpa_fourthdownline["Français"]["pe"] = "(du chinois 妹)";

storyofsounds_zjlimpa_firsttableline["Български"]["pe"] = "овца";
storyofsounds_zjlimpa_secondtableline["Български"]["pe"] = "сестричка";
storyofsounds_zjlimpa_firstdownline["Български"]["pe"] = "овца (от викането на овцата),";
storyofsounds_zjlimpa_seconddownline["Български"]["pe"] = "малка сестра, стажантка, жена по млада от";
storyofsounds_zjlimpa_thirddownline["Български"]["pe"] = "този който говори но по възрастна от колквото свойто";
storyofsounds_zjlimpa_fourthdownline["Български"]["pe"] = "първо дете (от китайски 妹)";



storyofsounds_zjlimpa_firsttableline["English"]["kr"] = "english";
storyofsounds_zjlimpa_secondtableline["English"]["kr"] = "sweet";
storyofsounds_zjlimpa_firstdownline["English"]["kr"] = "water (from Chinese 水),";
storyofsounds_zjlimpa_seconddownline["English"]["kr"] = "sweet (from English sweet)";
storyofsounds_zjlimpa_thirddownline["English"]["kr"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["kr"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["kr"] = "vivant,";
storyofsounds_zjlimpa_secondtableline["Français"]["kr"] = "une unité";
storyofsounds_zjlimpa_firstdownline["Français"]["kr"] = "préfixe pour les animaux vivants, vie (mot rimaïen)";
storyofsounds_zjlimpa_seconddownline["Français"]["kr"] = "une unité, une entité (toujours suivi de -i ou -po; mot rimaïen)";
storyofsounds_zjlimpa_thirddownline["Français"]["kr"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["kr"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["kr"] = "жив, една";
storyofsounds_zjlimpa_secondtableline["Български"]["kr"] = "единица";
storyofsounds_zjlimpa_firstdownline["Български"]["kr"] = "префикс за живи животрни (раримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["kr"] = "една единица (винаги следвено от -i или -po; раримайска дима)";
storyofsounds_zjlimpa_thirddownline["Български"]["kr"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["kr"] = "";



storyofsounds_zjlimpa_firsttableline["English"]["sq"] = "water,";
storyofsounds_zjlimpa_secondtableline["English"]["sq"] = "sweet";
storyofsounds_zjlimpa_firstdownline["English"]["sq"] = "water (from Chinese 水),";
storyofsounds_zjlimpa_seconddownline["English"]["sq"] = "sweet (from English sweet)";
storyofsounds_zjlimpa_thirddownline["English"]["sq"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["sq"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["sq"] = "eau";
storyofsounds_zjlimpa_secondtableline["Français"]["sq"] = "sucré";
storyofsounds_zjlimpa_firstdownline["Français"]["sq"] = "préfixe pour les animaux vivants, vie (mot rimaïen)";
storyofsounds_zjlimpa_seconddownline["Français"]["sq"] = "une unité, une entité (toujours suivi de -i ou -po; mot rimaïen)";
storyofsounds_zjlimpa_thirddownline["Français"]["sq"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["sq"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["sq"] = "жив, една";
storyofsounds_zjlimpa_secondtableline["Български"]["sq"] = "единица";
storyofsounds_zjlimpa_firstdownline["Български"]["sq"] = "префикс за живи животрни (раримайска дума)";
storyofsounds_zjlimpa_seconddownline["Български"]["sq"] = "една единица (винаги следвено от -i или -po; раримайска дима)";
storyofsounds_zjlimpa_thirddownline["Български"]["sq"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["sq"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["nki"] = "mouse";
storyofsounds_zjlimpa_secondtableline["English"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["nki"] = "mouse, Rarimaian, Italian (from the cry of a mouse)";
storyofsounds_zjlimpa_seconddownline["English"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["nki"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["nki"] = "souris";
storyofsounds_zjlimpa_secondtableline["Français"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["nki"] = "souris, rimaïen, italien (du cri d'une souris)";
storyofsounds_zjlimpa_seconddownline["Français"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["nki"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["nki"] = "мишка";
storyofsounds_zjlimpa_secondtableline["Български"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["nki"] = "мишка, раримайски, италиански (от викането на мишката)";
storyofsounds_zjlimpa_seconddownline["Български"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["nki"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["nki"] = "mouse";
storyofsounds_zjlimpa_secondtableline["English"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["English"]["nki"] = "mouse, Rarimaian, Italian (from the cry of a mouse)";
storyofsounds_zjlimpa_seconddownline["English"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["nki"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["nki"] = "souris";
storyofsounds_zjlimpa_secondtableline["Français"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["Français"]["nki"] = "souris, rimaïen, italien (du cri d'une souris)";
storyofsounds_zjlimpa_seconddownline["Français"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["nki"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["nki"] = "мишка";
storyofsounds_zjlimpa_secondtableline["Български"]["nki"] = "";
storyofsounds_zjlimpa_firstdownline["Български"]["nki"] = "мишка, раримайски, италиански (от викането на мишката)";
storyofsounds_zjlimpa_seconddownline["Български"]["nki"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["nki"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["nki"] = "";


storyofsounds_zjlimpa_firsttableline["English"]["khi"] = "bad,";
storyofsounds_zjlimpa_secondtableline["English"]["khi"] = "unpleasing";
storyofsounds_zjlimpa_firstdownline["English"]["khi"] = "bad, unpleasing (from the cry of an angry cat)";
storyofsounds_zjlimpa_seconddownline["English"]["khi"] = "";
storyofsounds_zjlimpa_thirddownline["English"]["khi"] = "";
storyofsounds_zjlimpa_fourthdownline["English"]["khi"] = "";

storyofsounds_zjlimpa_firsttableline["Français"]["khi"] = "mauvais,";
storyofsounds_zjlimpa_secondtableline["Français"]["khi"] = "déplaisant";
storyofsounds_zjlimpa_firstdownline["Français"]["khi"] = "mauvais, déplaisant (du cri d'un chat pas content)";
storyofsounds_zjlimpa_seconddownline["Français"]["khi"] = "";
storyofsounds_zjlimpa_thirddownline["Français"]["khi"] = "";
storyofsounds_zjlimpa_fourthdownline["Français"]["khi"] = "";

storyofsounds_zjlimpa_firsttableline["Български"]["khi"] = "лош,";
storyofsounds_zjlimpa_secondtableline["Български"]["khi"] = "неприятен";
storyofsounds_zjlimpa_firstdownline["Български"]["khi"] = "лош, неприятен (от викането на една нещастлива котка)";
storyofsounds_zjlimpa_seconddownline["Български"]["khi"] = "";
storyofsounds_zjlimpa_thirddownline["Български"]["khi"] = "";
storyofsounds_zjlimpa_fourthdownline["Български"]["khi"] = "";

storyofsounds_active = true;


storyoftheprimecity_factorization = [];
storyoftheprimecity_window = [];
storyoftheprimecity_door = [];
storyoftheprimecity_i = 2;
storyoftheprimecity_selected_number = 2;
storyoftheprimecity_x = 2;
storyoftheprimecity_y = 0;
storyoftheprimecity_hue = [];

function storyofsounds_english_play_syllable() {
	sound_to_play.pause();
	sound_to_play.currentTime = 0;
	
	pre_sound_to_play = document.getElementById("storyofsounds_english_" + english_speakers[Math.floor(Math.random() * english_speakers.length)] + "_" + storyofsounds_english_consonants[storyofsounds_english_consonant] + storyofsounds_english_vowels[storyofsounds_english_vowel]);
	if (pre_sound_to_play != null) {
		sound_to_play = pre_sound_to_play;
		sound_to_play.play();
	}
}

function storyofsounds_zjlimpa_play_syllable() {
	sound_to_play.pause();
	sound_to_play.currentTime = 0;
	
	pre_sound_to_play = document.getElementById("storyofsounds_zjlimpa_" + zjlimpa_speakers[Math.floor(Math.random() * zjlimpa_speakers.length)] + "_" + storyofsounds_zjlimpa_consonants[storyofsounds_zjlimpa_consonant] + storyofsounds_zjlimpa_vowels[storyofsounds_zjlimpa_vowel]);
	if (pre_sound_to_play != null) {
		sound_to_play = pre_sound_to_play;
		sound_to_play.play();
	}
}

turkish_random_number = getRandomInt(0, 9999);

turkish_tens = ["on", "yirmi", "otuz", "kırk", "elli", "altmış", "yetmiş", "seksen", "doksan"];
turkish_units = ["bir", "iki", "üç", "dört", "beş", "altı", "yedi", "sekiz", "dokuz"];

function turkish_get_number_all_in_letters(number) {
	if (number == 0) {
		return "sıfır";
	} else {
		number_data = number;
		thousands = Math.floor(number / 1000);
		return_string = "";
		
		if (thousands >= 1) {
			return_string += turkish_units[thousands - 1] + " bin";
		}
		
		number_data -= thousands*1000;
		
		hundreds = Math.floor(number_data / 100);
		
		if (hundreds >= 1) {
			if (return_string != "") {
				return_string += " ";
			}
			
			return_string += turkish_units[hundreds - 1] + " yüz";
		}
		
		number_data -= hundreds*100;
		
		tens = Math.floor(number_data / 10);
		
		if (tens >= 1) {
			if (return_string != "") {
				return_string += " ";
			}
			
			return_string += turkish_tens[tens - 1];
		}
		
		number_data -= tens*10;
		
		if (number_data >= 1) {
			if (return_string != "") {
				return_string += " ";
			}
			
			return_string += turkish_units[number_data - 1];
		}
		
		return return_string;
	}
}

turkish_random_number_all_in_letters = turkish_get_number_all_in_letters(turkish_random_number);

turkish_right_pressed = false;

turkish_random_number_exercise = "";
turkish_random_number_exercise_showing = ""; // With | to indicate where the typing goes

turkish_random_number_solution = false;


polish_random_number = getRandomInt(0, 9999);

polish_units = ["jeden", "dwa", "trzy", "cztery", "pięć", "sześć", "siedem", "osiem", "dziewięć", "dziesięć", "jedenaście", "dwanaście", "trzynaście", "czternaście", "piętnaście", "szesnaście", "siedemnaście", "osiemnaście", "dziewiętnaście"];
polish_tens = ["dwadzieścia", "trzydzieści", "czterdzieści", "pięćdziesiąt", "sześćdziesiąt", "siedemdziesiąt", "osiemdziesiąt", "dziewięćdziesiąt"];
polish_hundreds = ["sto", "dwieście", "trzysta", "czterysta", "pięćset", "sześćset", "siedemset", "osiemset", "dziewięćset"];
polish_thousands = ["tysiąc", "dwa tysiące", "trzy tysiące", "cztery tysiące", "pięć tysiący", "sześć tysiący", "siedem tysiący", "osiem tysiący", "dziewięć tysiący"];

function polish_get_number_all_in_letters(number) {
	if (number == 0) {
		return "zero";
	} else {
		number_data = number;
		thousands = Math.floor(number / 1000);
		return_string = "";
		
		if (thousands >= 1) {
			return_string += polish_thousands[thousands - 1];
		}
		
		number_data -= thousands*1000;
		
		hundreds = Math.floor(number_data / 100);
		
		if (hundreds >= 1) {
			if (return_string != "") {
				return_string += " ";
			}
			
			return_string += polish_hundreds[hundreds - 1];
		}
		
		number_data -= hundreds*100;
		
		if (number_data < 20 && number_data > 0) {
			if (return_string != "") {
				return_string += " ";
			}
			
			return_string += polish_units[number_data - 1];
		} else {
			tens = Math.floor(number_data / 10);
			
			if (tens >= 1) {
				if (return_string != "") {
					return_string += " ";
				}
				
				return_string += polish_tens[tens - 2];
			}
			
			number_data -= tens*10;
			
			if (number_data >= 1) {
				if (return_string != "") {
					return_string += " ";
				}
				
				return_string += polish_units[number_data - 1];
			}
		}
		
		return return_string;
	}
}

polish_random_number_all_in_letters = polish_get_number_all_in_letters(polish_random_number);

polish_right_pressed = false;

polish_random_number_exercise = "";
polish_random_number_exercise_showing = ""; // With | to indicate where the typing goes

polish_random_number_solution = false;

// 星期一，星期二，星期三，星期四，星期五，星期六，星期日

// poniedziałek wtorek środa czwartek piątek sobota niedziela
// pierwszy drugi trzeci czwarty piąty szósty

polish_days_of_the_week = ["poniedziałek", "wtorek", ""];
polish_day_numbers = ["pierwszego", "drugiego"];
polish_months = ["stycznia", ""];


japanese_kana_characters = ["あ", "ア", "い", "イ", "う", "ウ", "え", "エ", "お", "オ", "か",  "カ",  "き", "キ",  "く",  "ク",  "け",  "ケ",  "こ",  "コ",  "さ",  "サ",  "し",   "シ", "す",  "ス", "せ",  "セ",  "そ",  "ソ",  "た",  "タ",  "ち",   "チ",   "つ",   "ツ",   "て",  "テ",  "と",  "ト",  "な",  "ナ",  "に", "ニ",  "ぬ",  "ヌ",  "ね",  "ネ",  "の", "ノ",    "は",  "ハ",  "ひ",  "ヒ",  "ふ", "フ",  "へ",  "ほ",  "ホ",  "ま", "マ",  "み",  "ミ",  "む",  "ム",  "め",  "メ",  "も",  "モ", "や",  "ヤ",  "ゆ",  "ユ",  "よ",  "ヨ", "ら", "ラ",   "り",  "リ",  "る",  "ル",  "れ",  "レ",  "ろ",  "ロ", "わ",  "ワ"];
japanese_kana_readings =   ["a", "a", "i", "i", "u",  "u", "e", "e", "o", "o", "ka", "ka", "ki", "ki", "ku", "ku", "ke", "ke", "ko", "ko", "sa", "sa", "shi", "shi", "su", "su", "se", "se", "so", "so", "ta", "ta", "chi", "chi", "tsu",  "tsu", "te", "te", "to", "to", "na", "na", "ni", "ni", "nu", "nu", "ne", "ne", "no", "no",   "ha", "ha", "hi", "hi", "fu", "fu", "he", "ho", "ho", "ma", "ma", "mi", "mi", "mu", "mu", "me", "me", "mo", "mo", "ya", "ya", "yu", "yu", "yo", "yo", "ra", "ra", "ri", "ri", "ru", "ru", "re", "re", "ro", "ro", "wa", "wa"];
japanese_kana_hiragana =   [1,   0,   1,   0,   1,    0,   1,   0,   1,   0,   1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,     0,     1,      0,     1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,      1,    0,    1,    0,    1,    0,    2,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,    1,    0,     1,    0,    1,    0,    1,    0,    1,    0,    1,    0] // 0: katakana 1: hiragana 2: both

japanese_kana_random = getRandomInt(0, japanese_kana_characters.length - 1);
japanese_right_pressed = false;
japanese_kana_solution = false;
japanese_kana_exercise = "";
japanese_kana_exercise_showing = "";
japanese_kana_exercise_set = getRandomInt(0,2);

japanese_kana_name = [];
japanese_kana_name["English"] = ["Katakana", "Hiragana", "Both"];
japanese_kana_name["Français"] = ["Katakana", "Hiragana", "Les deux"];
japanese_kana_name["Български"] = ["Катакана", "Хирагана", "Двете"];

japanese_kana_description = [];

japanese_kana_description["English"] = [];
japanese_kana_description["English"][0] = "RIP AnARchy. An A in a CIRCLE with a cross above";

japanese_kana_description["Français"] = [];
japanese_kana_description["Français"][0] = "L'AnArchie est morte. A avec CERCLE autour et croix dessus";

japanese_kana_description["Български"] = [];
japanese_kana_description["Български"][0] = "АнАрхия е мъртва. A в един КРЪГ и един кръст на горе";
japanese_kana_description["Български"][1] = "КВАДРАТЕН А";


cherokee_characters = ["Ꭰ", "Ꭱ", "Ꭲ", "Ꭳ", "Ꭴ", "Ꭵ",    "Ꭶ", "Ꭷ", "Ꭸ", "Ꭹ", "Ꭺ", "Ꭻ", "Ꭼ",         "Ꭽ", "Ꭾ", "Ꭿ", "Ꮀ", "Ꮁ", "Ꮂ",                                   "Ꮃ", "Ꮄ", "Ꮅ", "Ꮆ", "Ꮇ", "Ꮈ",        "Ꮉ", "Ꮊ", "Ꮋ", "Ꮌ", "Ꮍ", 	      "Ꮎ", "Ꮏ", "Ꮐ", "Ꮑ", "Ꮒ", "Ꮓ", "Ꮔ", "Ꮕ",                                                     "Ꮖ", "Ꮗ", "Ꮘ", "Ꮙ", "Ꮚ", "Ꮛ",             "Ꮝ", "Ꮜ", "Ꮞ", "Ꮟ", "Ꮠ", "Ꮡ", "Ꮢ",          "Ꮣ", "Ꮤ", "Ꮥ", "Ꮦ", "Ꮧ", "Ꮨ", "Ꮩ",	"Ꮪ", "Ꮫ",	                             "Ꮬ", "Ꮭ", "Ꮮ", 	"Ꮯ", "Ꮰ", "Ꮱ", "Ꮲ", 	           "Ꮳ", "Ꮴ", "Ꮵ", "Ꮶ", "Ꮷ", "Ꮸ",               "Ꮹ", "Ꮺ", "Ꮻ", "Ꮼ", "Ꮽ", "Ꮾ",      "Ꮿ", "Ᏸ", "Ᏹ", "Ᏺ", "Ᏻ", "Ᏼ"];

cherokee_readings = ["a", "e", "i", "o", "u", "v",     "ga", "ka", "ge", "gi", "go", "gu", "gv",  "ha", "he", "hi", "ho", "hu", "hv",                              "la", "le", "li", "lo", "lu", "lv",        "ma", "me", "mi", "mo", "mu",   "na", "hna", "nah", "ne", "ni", "no", "nu", "nv",                                       "qua", "que", "qui", "quo", "quu", "quv",  "s", "sa", "se", "si", "so", "su", "sv",    "da", "ta", "de", "te", "di", "ti", "do", "du", "dv",                       "dla", "tla", "tle", "tli", "tlo", "tlu", "tlv", "tsa", "tse", "tsi", "tso", "tsu", "tsv",   "wa", "we", "wi", "wo", "wu", "wv", "ya", "ye", "yi", "yo", "yu", "yv"];

cherokee_right_pressed = false;

cherokee_random = getRandomInt(0, cherokee_characters.length - 1);

cherokee_solution = false;

cherokee_exercise = "";
cherokee_exercise_showing = "";


computer_text = "";

computer_text_showing = [];
computer_bigtext = [];

for (i = 1; i < 1000000; i++) {
	computer_text_showing[i] = [];
	computer_bigtext[i - 1] = [];
}

computer_text_cursor_position_line = 1;
computer_text_cursor_position_column = 1;

computer_text_keyboard = 0; // 0 = bépo the suhin version (Ctrl + Caps Lock + L) 1 = cyrillic (Ctrl + Caps Lock + K) 2 = toki pona (Ctrl + Caps Lock + P) 3 = emoji (Ctrl + Caps Lock + E)

computer_text_tech_keyboard_number = 0; // 0 = Web (HTML5, Javascript, AJAX, CSS3, PHP, MySQL. Ctrl + Caps Lock + H)

computer_text_tech_keyboard = false;

computer_text_circumflex = false;
computer_text_umlaut = false;
computer_text_acute = false;
computer_text_double_acute = false;



function computer_bigtext_set() {
	for (i = 0; i < 10; i++) {
		for (j = 0; j < 12; j += 4) {
			if (getRandomInt(1,2) == 1) {
				computer_bigtext[i][j] = "P";
				computer_bigtext[i][j+1] = "u";
				computer_bigtext[i][j+2] = "r";
				computer_bigtext[i][j+3] = "e";
			} else {
				computer_bigtext[i][j] = "V";
				computer_bigtext[i][j+1] = "o";
				computer_bigtext[i][j+2] = "i";
				computer_bigtext[i][j+3] = "d";
			}
		}
	}
}

function computer_text_add_character(character) {
	if (tad_1001_dialog_shown) {
		tad_1001_dialog_content[tad_1001_dialog_content.length] = character;
	} else {
		for (i = computer_text_showing[computer_text_cursor_position_line].length - 1; i >= computer_text_cursor_position_column; i--) {
			computer_text_showing[computer_text_cursor_position_line][i+1] = computer_text_showing[computer_text_cursor_position_line][i];
		}
		
		computer_text_showing[computer_text_cursor_position_line][computer_text_cursor_position_column] = character;
		
		computer_text_cursor_position_column++;
		
		computer_bigtext_set();
	}
}

function computer_text_add_break() {
	for (i = 99999; i < computer_text_cursor_position_line + 1; i--) {
		computer_text_showing[i + 1] = computer_text_showing[i];
	}
	
	computer_text_showing[computer_text_cursor_position_line + 1].length = 1;
	
	for (i = 1; i <= computer_text_showing[computer_text_cursor_position_line].length - computer_text_cursor_position_column; i++) {
		computer_text_showing[computer_text_cursor_position_line + 1][i] = computer_text_showing[computer_text_cursor_position_line][computer_text_cursor_position_column + i - 1];
	}
	
	computer_text_showing[computer_text_cursor_position_line].length = computer_text_cursor_position_column;

	computer_text_cursor_position_line++;
	computer_text_cursor_position_column = 1;
}

computer_freshly_chosen = true;

tad_1001_dialog_shown = false;
tad_1001_dialog_title = "";
tad_1001_dialog_content = [];

temp_text = "";

test_3d_freshly_chosen = true;

function step() {
	//
	//
	// Pre-activicties
	//
	//

	craftmans_solution = -craftmans_solution;
	
	
	//
	// Resize the canvas
	//
	
	if (window.innerWidth < window.innerHeight * 16 / 9) {
		//console.log("if");
		if (!main_menu && learned_language == 15) {
			canvas3d.height = window.innerWidth * 17 / 18 *0.998 / 2;
			canvas3d.width = canvas3d.height * 18 / 17 * 16 / 9;
			canvas3d.style.margin = "0 0";
			canvas.width = canvas3d.width;
			canvas.height = canvas3d.height/17;
			canvas.style.margin = "0 0";
			//canvas3d.style.margin = "" + ((window.innerHeight - canvas3d.height) / 2)  + "px auto";
			
			three_dimensions_shown = true;
		} else {
			canvas.width = window.innerWidth * 0.97;
			canvas.height = window.innerWidth * 9 / 16 * 0.97;
			canvas.style.margin = "" + ((window.innerHeight - canvas.height) / 2)  + "px auto";
			canvas3d.width = 0;
			canvas3d.height = 0;
			canvas3d.style.margin = "0 0";
			
			three_dimensions_shown = false;
		}
	} else {
		//console.log("else");
		if (!main_menu && learned_language == 15) {
			canvas3d.height = window.innerHeight * 17 / 18 * 0.97;
			canvas3d.width = canvas3d.height * 18 / 17 * 16 / 9;
			
			canvas3d.style.margin = "0 0";
			/*canvas3d.height = window.innerWidth *0.998 / 2;
			canvas3d.width = canvas3d.height * 16 / 9;
			canvas3d.style.margin = "0 auto";*/
			canvas.width = canvas3d.width;
			canvas.height = canvas3d.height/17;
			canvas.style.margin = "0 0";
			
			three_dimensions_shown = true;
		} else {
			canvas.width = window.innerHeight * 16 / 9 *0.998;
			canvas.height = window.innerHeight *0.998;
			canvas.style.margin = "0 auto";
			canvas3d.width = 0;
			canvas3d.height = 0;
			canvas3d.style.margin = "0 0";
			
			three_dimensions_shown = false;
		}
	}
	
	// Fill the canvas with black
	context.fillStyle = "black";
	context.fillRect(0,0,canvas.width,canvas.height);
	
	//
	//
	// Variant-activities
	//
	//
	
	if (miliseconds < 2000) {
		// Write "Story Of Students"
		context.fillStyle = "white";
		context.font = "" + canvas.height/2 + "px monospace";
		context.fillText("Suhin", canvas.width/120, canvas.height/2);
		
		context.font = "" + canvas.height/20 + "px monospace";
		context.fillText("Standardless Universal Hybrid Intelligence Network", canvas.width/120, 3*canvas.height/4);
	} else {
		if (main_menu == true) {
			// Moving
			if (key_freshly_pressed["ArrowLeft"]) {
				learning_language--;
				if (learning_language < 0) {
					learning_language = learning_languages.length - 1;
				}
				
				if (learned_languages[learned_language] == learning_languages[learning_language]) {
					learned_language++;
					
					if (learned_language >= learned_languages.length) {
						learned_language = 0;
					}
				}
			}
			
			if (key_freshly_pressed["ArrowRight"]) {
				learning_language++;
				if (learning_language >= learning_languages.length) {
					learning_language = 0;
				}
				
				if (learned_languages[learned_language] == learning_languages[learning_language]) {
					learned_language++;
					
					if (learned_language >= learned_languages.length) {
						learned_language = 0;
					}
				}
			}
			
			if (key_freshly_pressed["ArrowUp"]) {
				learned_language--;
				if (learned_language < 0) {
					learned_language = learned_languages.length - 1;
				}
				if (learned_languages[learned_language] == learning_languages[learning_language]) {
					learned_language--;
					if (learned_language < 0) {
						learned_language = learned_languages.length - 1;
					}
				}
			}
			
			if (key_freshly_pressed["ArrowDown"]) {
				learned_language++;
				if (learned_language >= learned_languages.length) {
					learned_language = 0;
				}
				if (learned_languages[learned_language] == learning_languages[learning_language]) {
					learned_language++;
					if (learned_language >= learned_languages.length) {
						learned_language = 0;
					}
				}
			}
			
			if (key_freshly_pressed["Digit1"] || key_freshly_pressed["Numpad1"]) {
				if (learned_languages[learned_language] == "English") {
					window.open("https://w3techs.com/technologies/overview/content_language/all",'_blank');
				}
			}
			
			if (key_freshly_pressed["Digit2"] || key_freshly_pressed["Numpad2"]) {
				if (learned_languages[learned_language] == "English") {
					window.open("https://www.heritage.org/index/ranking",'_blank');
				}
			}
			
			if (key_freshly_pressed["Digit3"] || key_freshly_pressed["Numpad3"]) {
				if (learned_languages[learned_language] == "English") {
					window.open("https://www.transparency.org/news/feature/corruption_perceptions_index_2017",'_blank');
				}
			}
			
			if (key_freshly_pressed["Digit3"] || key_freshly_pressed["Numpad3"]) {
				if (learned_languages[learned_language] == "English") {
					window.open("https://www.transparency.org/news/feature/corruption_perceptions_index_2017",'_blank');
				}
			}
			
			if (key_freshly_pressed["Space"] || key_freshly_pressed["Enter"] || key_freshly_pressed["NumpadEnter"] || key_freshly_pressed["ControlLeft"]) {
				main_menu = false;
				
				/*music_menu.pause();
				music_menu.currentTime = 0;
				
				music_peace.play();*/
				
				
			}
			
			if (clicking == true) {
				if (clicking_y < Math.ceil(17*canvas.height/18) && clicking_y > Math.floor(4*canvas.height/5)) {
					main_menu = false;
					
					/*music_menu.pause();
					music_menu.currentTime = 0;

					music_peace.play();*/
				} else if (clicking_y < Math.ceil(4*canvas.height/5) && clicking_y > Math.floor(4*canvas.height/5 - canvas.height/35)) {
					for (i = 0; i < 7; i++) {
						if (clicking_x > Math.floor(canvas.width/200 + i*canvas.width/7) && clicking_x < Math.ceil(canvas.width/200 + (i+1)*canvas.width/7)) {
							learning_language += i - 3;
							
							if (learning_language < 0) {
								learning_language += learning_languages.length;
							}
							
							if (learning_language >= learning_languages.length) {
								learning_language -= learning_languages.length;
							}
							
							if (learned_languages[learned_language] == learning_languages[learning_language]) {
								learned_language++;
								
								if (learned_language >= learned_languages.length) {
									learned_language = 0;
								}
							}
						}
					}
				} else if (clicking_x < canvas.width/7) {
					for (i = 0; i < learned_languages.length; i++) {
						if (clicking_y >= Math.floor(canvas.height/15 + (i-1)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + i*canvas.height/30)) {
							if (learned_languages[i] != learning_languages[learning_language]) {
								learned_language = i;
								break;
							}
						}
					}
				} else if (learned_languages[learned_language] == "English" && clicking_y >= Math.floor(canvas.height/15 + (-1)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 0*canvas.height/30)) {
					window.open("https://w3techs.com/technologies/overview/content_language/all",'_blank');
				} else if (learned_languages[learned_language] == "English" && clicking_y >= Math.floor(canvas.height/15 + (0)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 1*canvas.height/30)) {
					window.open("https://www.heritage.org/index/ranking",'_blank');
				} else if (learned_languages[learned_language] == "English" && clicking_y >= Math.floor(canvas.height/15 + (1)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 8*canvas.height/30)) {
					window.open("https://www.transparency.org/news/feature/corruption_perceptions_index_2017",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Français" && clicking_y >= Math.floor(canvas.height/15 + (9)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 10*canvas.height/30)) {
					window.open("https://w3techs.com/technologies/overview/content_language/all",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Français" && clicking_y >= Math.floor(canvas.height/15 + (10)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 11*canvas.height/30)) {
					window.open("https://www.heritage.org/index/ranking",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Français" && clicking_y >= Math.floor(canvas.height/15 + (11)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 18*canvas.height/30)) {
					window.open("https://www.transparency.org/news/feature/corruption_perceptions_index_2017",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Български" && clicking_y >= Math.floor(canvas.height/15 + (9)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 10*canvas.height/30)) {
					window.open("https://w3techs.com/technologies/overview/content_language/all",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Български" && clicking_y >= Math.floor(canvas.height/15 + (10)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 11*canvas.height/30)) {
					window.open("https://www.heritage.org/index/ranking",'_blank');
				} else if (learned_languages[learned_language] == "English" && learning_languages[learning_language] == "Български" && clicking_y >= Math.floor(canvas.height/15 + (11)*canvas.height/30) && clicking_y <= Math.ceil(canvas.height/15 + 18*canvas.height/30)) {
					window.open("https://www.transparency.org/news/feature/corruption_perceptions_index_2017",'_blank');
				}
			}
			
			// Sound
			if (language_spoken != learned_language) {
				sound_to_play.pause();
				sound_to_play.currentTime = 0;
				
				if (learned_language == 0) { // English
					sound_to_play = document.getElementById("language_description_english_" + english_speakers[Math.floor(Math.random() * english_speakers.length)]);
					sound_to_play.play();
				}
				
				
				
				language_spoken = learned_language;
			}
			
			context.strokeStyle = "white"
			context.lineWidth = canvas.height / 500;
			/*
			context.arc(canvas.width/50, canvas.height/50, canvas.height/60, 0, Math.PI*2);
			context.stroke();
			
			context.arc(canvas.width/50, canvas.height/50, canvas.height/100, 0, Math.PI*2);
			context.stroke();
			
			context.arc(canvas.width/50, canvas.height/50, canvas.height/170, 0, Math.PI*2);
			context.stroke();*/
			
			// Learned languages
			
			context.font = "" + canvas.height/30 + "px monospace";
			context.fillText("😘", canvas.width/200, canvas.height/30);
			
			for (i = learned_language - 5; i <= learned_language + 5; i++) {

				
				context.font = "" + canvas.height/35 + "px monospace";
				effective_i = i;
				if (effective_i < 0) {
					effective_i += learned_languages.length;
				}
				if (effective_i >= learned_languages.length) {
					effective_i -= learned_languages.length;
				}
				
				if (learning_languages[learning_language] == learned_languages[effective_i]) {
					context.fillStyle = "rgb(128,128,128)";
				} else if (i == learned_language) {
					context.fillStyle = "yellow";
				} else {
					context.fillStyle = "white";
				}
				context.fillText(learned_languages[effective_i], canvas.width/200, canvas.height/15 + (i - learned_language + 5)*canvas.height/19);
			}
			
			// Learning languages
			
			context.font = "" + canvas.height/40 + "px monospace";
			context.fillText("🙂", canvas.width/200, 3*canvas.height/4);
			
			
			
			for (i = learning_language - 3; i <= learning_language + 3; i++) {
				real_i = i;
				
				while (real_i < 0) {
					real_i += learning_languages.length;
				}
				
				while (real_i >= learning_languages.length) {
					real_i -= learning_languages.length;
				}
				
				if (real_i == learning_language) {
					context.fillStyle = "yellow";
				} else {
					context.fillStyle = "white";
				}
				
				context.font = "" + canvas.height/35 + "px monospace";
				
				context.fillText(learning_languages[real_i], canvas.width/200 + (i - learning_language + 3) * canvas.width/7, 4*canvas.height/5);
			}
			
			// Description of the learned language in itself and in the learning language
			
			// Decoration for English
			
			if (learned_language == 0) {
				context.fillStyle = "rgb(0,255,255)";
				context.fillRect(canvas.width/4.3, 0, canvas.width, 3*canvas.height/4);
				
				context.beginPath();
				
				context.fillStyle = "rgb(255,255,255)";
				
				context.moveTo(canvas.width/4.3, 0);
				context.quadraticCurveTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2, canvas.height/7, canvas.width, 0);
				
				context.fill();
				
				
				//
				// Draw a reverted dog
				//
				
				context.fillStyle = "rgb(150,75,0)"; // The dog is brown
				
				context.beginPath();
				context.moveTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/7 - canvas.width/100, canvas.height/18);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/7 - canvas.width/100, canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4, canvas.height/6);
				
				// Head
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4, canvas.height/6 + canvas.height/10);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 - canvas.width/13, canvas.height/6 + canvas.height/10);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 - canvas.width/13, 2*canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 - canvas.width/39, 2*canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 - canvas.width/39, 2*canvas.height/6 + canvas.height/18);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 + canvas.width/39, 2*canvas.height/6 + canvas.height/18);
				
				// Tail
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/4 + canvas.width/39, canvas.height/6 + canvas.height/8);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/4, canvas.height/6 + canvas.height/8);
				context.lineTo(canvas.width, 2*canvas.height/5);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/4, canvas.height/6 + 2*canvas.height/24);
				
				// Back paw
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/4, canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/7 + canvas.width/100, canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/7 + canvas.width/100, canvas.height/18);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/7 - canvas.width/100, canvas.height/18);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 + canvas.width/7 - canvas.width/100, canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/7 + canvas.width/100, canvas.height/6);
				context.lineTo(canvas.width/4.3 + (canvas.width - canvas.width/4.3) / 2 - canvas.width/7 + canvas.width/100, canvas.height/18);
				
				context.fill();
				
				//
				// Draw the bubble for the text
				//
				context.fillStyle = "rgb(255,200,255)";
				context.beginPath();
				context.moveTo(canvas.width/3 + canvas.width/40, 2*canvas.height/5);
				context.lineTo(canvas.width/3 + canvas.width/40, 2*canvas.height/5 + canvas.height/50);
				context.lineTo(canvas.width/4.3 + canvas.width/200, 2*canvas.height/5 + canvas.height/50);
				context.lineTo(canvas.width/4.3 + canvas.width/200, 3*canvas.height/4 - canvas.height/200);
				context.lineTo(canvas.width - canvas.width/200, 3*canvas.height/4 - canvas.height/200);
				context.lineTo(canvas.width - canvas.width/200, 2*canvas.height/5 + canvas.height/50);
				context.lineTo(canvas.width/3 + canvas.width/40 + canvas.width/200, 2*canvas.height/5 + canvas.height/50);
				
				context.fill();
				
				context.fillStyle = "rgb(0,0,0)";
				context.font = "italic " + canvas.height/6 + "px monospace";
				context.fillText("I bless", canvas.width/4.3 + canvas.width/100, 2.7*canvas.height/4 - canvas.height/200);
				
				
				// The United States flag is 10/19. It consists of 13 stripes, starting with red, then white, red, …, red
				
				
				for (i = 0; i < 13; i++) {
					if (i % 2 == 0) {
						context.fillStyle = "rgb(155,0,0)";
					} else {
						context.fillStyle = "rgb(255,255,255)";
					}
					
					context.fillRect(2*canvas.width/3, 2*canvas.height/5 + canvas.height/25 + i*10*canvas.width/60/13, 19*canvas.width/60, 10*canvas.width/60/13);
				}
				
				// 617/1544 ~= 2/5
				
				// 7/13
				
				context.fillStyle = "rgb(0,0,155)";
				
				context.fillRect(2*canvas.width/3, 2*canvas.height/5 + canvas.height/25, 2*19*canvas.width/60/5, 7*10*canvas.width/60/13);
				
				context.fillStyle = "rgb(255,255,255)";
				
				for (i = 0; i < 9; i++) {
					for (j = 0; j < 6 - (i % 2); j++) {
						context.beginPath();
						if (i == 0 && j == 0) {
							console.log("Coordinates: " + 2*canvas.width/3 + canvas.width/50 + (i % 2)*canvas.width/100 + j*canvas.width/50 + canvas.width/130*Math.cos(0*Math.PI*2/5+3*Math.PI/2 + "; " + 2*canvas/5 + canvas.height/20 + i*canvas.height/44 + canvas.width/130*Math.sin(0*Math.PI*2/5+3*Math.PI/2)));
						}
						context.moveTo(2*canvas.width/3 + canvas.width/90 + (i % 2)*canvas.width/90 + j*canvas.width/47 + canvas.width/180*Math.cos(0*Math.PI*2/5+3*Math.PI/2), 2*canvas.height/5 + canvas.height/18 + i*canvas.height/60 + canvas.width/180*Math.sin(0*Math.PI*2/5+3*Math.PI/2));
						context.lineTo(2*canvas.width/3 + canvas.width/90 + (i % 2)*canvas.width/90 + j*canvas.width/47 + canvas.width/180*Math.cos(2*Math.PI*2/5+3*Math.PI/2), 2*canvas.height/5 + canvas.height/18 + i*canvas.height/60 + canvas.width/180*Math.sin(2*Math.PI*2/5+3*Math.PI/2));
						context.lineTo(2*canvas.width/3 + canvas.width/90 + (i % 2)*canvas.width/90 + j*canvas.width/47 + canvas.width/180*Math.cos(4*Math.PI*2/5+3*Math.PI/2), 2*canvas.height/5 + canvas.height/18 + i*canvas.height/60 + canvas.width/180*Math.sin(4*Math.PI*2/5+3*Math.PI/2));
						context.lineTo(2*canvas.width/3 + canvas.width/90 + (i % 2)*canvas.width/90 + j*canvas.width/47 + canvas.width/180*Math.cos(6*Math.PI*2/5+3*Math.PI/2), 2*canvas.height/5 + canvas.height/18 + i*canvas.height/60 + canvas.width/180*Math.sin(6*Math.PI*2/5+3*Math.PI/2));
						context.lineTo(2*canvas.width/3 + canvas.width/90 + (i % 2)*canvas.width/90 + j*canvas.width/47 + canvas.width/180*Math.cos(8*Math.PI*2/5+3*Math.PI/2), 2*canvas.height/5 + canvas.height/18 + i*canvas.height/60 + canvas.width/180*Math.sin(8*Math.PI*2/5+3*Math.PI/2));
						context.fill();
					}
				}
			}
			
			context.font = "" + canvas.height/35 + "px monospace";
			
			
			for (i = 0; i < language_description[learned_languages[learned_language]][learned_languages[learned_language]].length; i++) {
				context.fillStyle = "rgb(" + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ")";
				context.fillText(language_description[learned_languages[learned_language]][learned_languages[learned_language]][i], canvas.width/200 + canvas.width/4.3, canvas.height/15 + i*canvas.height/30);
			}
			
			for (i = 0; i < language_description[learned_languages[learned_language]][learning_languages[learning_language]].length; i++) {
				context.fillStyle = "rgb(" + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ")";
				context.fillText(language_description[learned_languages[learned_language]][learning_languages[learning_language]][i], canvas.width/200 + canvas.width/4.3, canvas.height/15 + (i+10)*canvas.height/30);
			}
			
			// Button for entering the world
			context.font = "" + canvas.height/30 + "px monospace";
			context.fillStyle = "red";
			context.fillText(enter_world[learned_languages[learned_language]], canvas.width/200, 17*canvas.height/18 - canvas.width/200 - canvas.height/30);
			
			context.font = "" + canvas.height/40 + "px monospace";
			context.fillText(enter_world[learning_languages[learning_language]], canvas.width/20, 17*canvas.height/18 - canvas.width/200 - canvas.height/90);
			
			english_freshly_chosen = true;
			toki_pona_freshly_chosen = true;
		} else {
			/*if (learned_language == 0) { // English
				//
				// Draw the hero
				//
				
				// Draw the block
				fill = "rgb(" + hero_background_color_red + "," + hero_background_color_green + "," + hero_background_color_blue + ")";
				context.fillStyle = fill;
				context.fillRect(canvas.width/2,canvas.height/2,canvas.width/32,canvas.height/18);
				
				// Draw the text
				// Get the letter
				letter = hero_name_in_pieces[Math.floor(miliseconds / hero_letter_change) % hero_name_in_pieces.length];
				
				if (getRandomInt(1,3) == 1) {
					context.fillStyle = "white";
				} else {
					context.fillStyle = "rgb(" + hero_text_color_red + "," + hero_text_color_green + "," + hero_text_color_blue + ")";
				}
				
				context.font = "" + canvas.height/30 + "px monospace";
				context.fillText(letter, canvas.width/2 + canvas.width/123, canvas.height/2 + canvas.height/30);
			*/
			if (learned_language == 0) { // English
				if (english_freshly_chosen) {	
					sound_to_play.pause();
					sound_to_play.currentTime = 0;
					
					english_freshly_chosen = false;
				}
				
				for (i = 0; i < english_original_positions.length; i++) {
					for (j = 0; j < english_original_positions[i].length; j++) {
						if (english_background_color_used_red[i][j] == -2) {
							context.fillStyle = "rgb(128,255,255)";
							context.fillRect(canvas.width/32*j,canvas.height/18*i,5*canvas.width/32,canvas.height/18);
							
							context.fillStyle = "rgb(0,255,255)";
							context.fillRect(canvas.width/32*(j+5),canvas.height/18*i,4*canvas.width/32,canvas.height/18);
							
							context.fillStyle = "rgb(0,0,0)";
							
							prime_numbers_before_50 = "2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 47, … ";
							
							initialisation = Math.floor((miliseconds % (54 * 500)) / 500);
							
							context.font = "" + canvas.height/70 + "px monospace";
							
							for (k = initialisation + 1; k <= initialisation + 14; k++) {
								context.fillText(prime_numbers_before_50[k%54], canvas.width/32*j + canvas.width/96 * (k - (miliseconds % (54 * 500)) / 500), canvas.height/18*i + canvas.height/36 + canvas.height/100 + (canvas.height/36) / 2 * Math.cos(k*0.3 + miliseconds / 200 / Math.PI));
							}
							
							context.fillStyle = "rgb(150,75,0)";
							
							context.beginPath();
							
							context.moveTo(canvas.width/32*(j+5) + canvas.width/200, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+6), canvas.height/18*i + canvas.height/200);
							
							context.lineTo(canvas.width/32*(j+7) - canvas.width/200, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+7) - canvas.width/100, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+7) - canvas.width/100, canvas.height/18*(i+1));
							
							context.lineTo(canvas.width/32*(j+5) + canvas.width/100, canvas.height/18*(i+1));
							
							context.lineTo(canvas.width/32*(j+5) + canvas.width/100, canvas.height/18*i + canvas.height/36);
							
							context.closePath();
							
							context.fill();
							
							context.beginPath();
							
							context.moveTo(canvas.width/32*(j+7) + canvas.width/200, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+8), canvas.height/18*i + canvas.height/200);
							
							context.lineTo(canvas.width/32*(j+9) - canvas.width/200, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+9) - canvas.width/100, canvas.height/18*i + canvas.height/36);
							
							context.lineTo(canvas.width/32*(j+9) - canvas.width/100, canvas.height/18*(i+1));
							
							context.lineTo(canvas.width/32*(j+7) + canvas.width/100, canvas.height/18*(i+1));
							
							context.lineTo(canvas.width/32*(j+7) + canvas.width/100, canvas.height/18*i + canvas.height/36);
							
							context.closePath();
							
							context.fill();
							
							context.font = "" + canvas.height/30 + "px monospace";
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("P", canvas.width/32*j + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("r", canvas.width/32*(j+1) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("i", canvas.width/32*(j+2) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("m", canvas.width/32*(j+3) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("e", canvas.width/32*(j+4) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("t", canvas.width/32*(j+5) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("o", canvas.width/32*(j+6) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("w", canvas.width/32*(j+7) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
							context.fillText("n", canvas.width/32*(j+8) + canvas.width/118, canvas.height/18*i + canvas.height/29);
							
							context.fillStyle = "yellow";
							context.fillRect(canvas.width/32*j,canvas.height/18*i,9*canvas.width/32,canvas.height/360);
							context.fillRect(canvas.width/32*j,canvas.height/18*i,canvas.width/640,canvas.height/18);
							context.fillRect(canvas.width/32*j,canvas.height/18*(i+1)-canvas.height/360,9*canvas.width/32,canvas.height/360);
							context.fillRect(canvas.width/32*(j+9)-canvas.width/640,canvas.height/18*i,canvas.width/640,canvas.height/18);
							
							j += 8;
						} else {
							if (english_background_color_used_red[i][j] >= 0) {
								context.fillStyle = "rgb(" + english_background_color_used_red[i][j] + ", " + english_background_color_used_green[i][j] + ", " + english_background_color_used_blue[i][j] + ")";
								context.fillRect(canvas.width/32*j,canvas.height/18*i,canvas.width/32,canvas.height/18);
							} else if (english_background_color_used_red[i][j] == -1) { // Waves
								context.fillStyle = "rgb(0,255,255)";
								
								english_background_color_used_red[i][j] + ", " + english_background_color_used_green[i][j] + ", " + english_background_color_used_blue[i][j] + ")";
								context.fillRect(canvas.width/32*j,canvas.height/18*i,canvas.width/32,canvas.height/18);
								
								context.fillStyle = "rgb(0,0,255)";
								
								context.beginPath();
								
								context.moveTo(canvas.width/32*j,canvas.height/18*(i+1));
								
								for (k = 0; k <= 30; k++) {
									context.lineTo(canvas.width/32*(j + k/30),canvas.height/18*(i) + canvas.height/120 + Math.cos(k*Math.PI/15 + miliseconds/700) * canvas.width/18/20);
								}
								
								context.lineTo(canvas.width/32*(j+1),canvas.height/18*(i+1));
								
								context.closePath();
								context.fill();
							}
							
							context.fillStyle = "rgb(" + english_barrier_color_used_red[i][j] + ", " + english_barrier_color_used_green[i][j] + ", " + english_barrier_color_used_blue[i][j] + ")";
							
							if (english_top_barrier[i][j] == true) {
								context.fillRect(canvas.width/32*j,canvas.height/18*i,canvas.width/32,canvas.height/360);
							}
							
							if (english_left_barrier[i][j] == true) {
								context.fillRect(canvas.width/32*j,canvas.height/18*i,canvas.width/640,canvas.height/18);
							}
							
							if (english_bottom_barrier[i][j] == true) {
								context.fillRect(canvas.width/32*j,canvas.height/18*(i+1)-canvas.height/360,canvas.width/32,canvas.height/360);
							}
							
							if (english_right_barrier[i][j] == true) {
								context.fillRect(canvas.width/32*(j+1)-canvas.width/640,canvas.height/18*i,canvas.width/640,canvas.height/18);
							}
							
							context.fillStyle = "rgb(" + english_text_color_used_red[i][j] + ", " + english_text_color_used_green[i][j] + ", " + english_text_color_used_blue[i][j] + ")";
							
							context.font = "" + canvas.height/30 + "px monospace";
							if (english_original_positions[i][j] == "气") {
								if (miliseconds%2100 < 700) {
									context.fillText("G", canvas.width/32*j + canvas.width/118, canvas.height/18*i + canvas.height/29);
								} else if (miliseconds%2100 < 1400) {
									context.fillText("a", canvas.width/32*j + canvas.width/118, canvas.height/18*i + canvas.height/29);
								} else {
									context.fillText("s", canvas.width/32*j + canvas.width/118, canvas.height/18*i + canvas.height/29);
								}
							} else {
								context.fillText(english_original_positions[i][j], canvas.width/32*j + canvas.width/118, canvas.height/18*i + canvas.height/29);
							}
						}
					}
				}
				
				context.fillStyle = "rgba(255,0,0,0.3)";
				context.font = "" + canvas.height/4 + "px monospace";
				
				context.fillText("S", canvas.width/100, canvas.height/18*4 + canvas.height/7);
				context.fillText("t", canvas.width/8 + canvas.width/100, canvas.height/18*4 + canvas.height/7);
				
				if (0) {
					//
					//
					// Story Of Sounds
					//
					//
					
					// Start
					if (english_freshly_chosen) {
						sound_to_play.pause();
						sound_to_play.currentTime = 0;
						
						storyofsounds_english_play_syllable();
						english_freshly_chosen = false;
					}
					
					//
					// Actions
					//
					storyofsounds_speed = 1;
					
					if (key_pressed["Space"]) {
						storyofsounds_speed = 4;
					}
					
					if (key_freshly_pressed["ArrowLeft"]) {
						storyofsounds_english_vowel -= storyofsounds_speed;
						
						if (storyofsounds_english_vowel < 0) {
							storyofsounds_english_vowel = storyofsounds_english_vowels.length + storyofsounds_english_vowel;
						}
						
						storyofsounds_english_view_vowel = storyofsounds_english_vowel - 3;
						
						if (storyofsounds_english_view_vowel < 0) {
							storyofsounds_english_view_vowel = 0;
						}
						
						if (storyofsounds_english_view_vowel > storyofsounds_english_vowels.length - 5) {
							storyofsounds_english_view_vowel = storyofsounds_english_vowels.length - 5;
						}
						
						storyofsounds_english_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowRight"]) {
						storyofsounds_english_vowel += storyofsounds_speed;
						
						if (storyofsounds_english_vowel >= storyofsounds_english_vowels.length) {
							storyofsounds_english_vowel -= storyofsounds_english_vowels.length;
						}
						
						storyofsounds_english_view_vowel = storyofsounds_english_vowel - 1;
						
						if (storyofsounds_english_view_vowel < 0) {
							storyofsounds_english_view_vowel = 0;
						}
						
						if (storyofsounds_english_view_vowel > storyofsounds_english_vowels.length - 5) {
							storyofsounds_english_view_vowel = storyofsounds_english_vowels.length - 5;
						}
						
						storyofsounds_english_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowUp"]) {
						storyofsounds_english_consonant -= storyofsounds_speed;
						
						if (storyofsounds_english_consonant < 0) {
							storyofsounds_english_consonant = storyofsounds_english_consonants.length + storyofsounds_english_consonant;
						}
						
						storyofsounds_english_view_consonant = storyofsounds_english_consonant - 4;
						
						if (storyofsounds_english_view_consonant < 0) {
							storyofsounds_english_view_consonant = 0;
						}
						
						if (storyofsounds_english_view_consonant > storyofsounds_english_consonants.length - 6) {
							storyofsounds_english_view_consonant = storyofsounds_english_consonants.length - 6;
						}
						
						storyofsounds_english_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowDown"]) {
						storyofsounds_english_consonant += storyofsounds_speed;
						
						if (storyofsounds_english_consonant >= storyofsounds_english_consonants.length) {
							storyofsounds_english_consonant -= storyofsounds_english_consonants.length;
						}
						
						storyofsounds_english_view_consonant = storyofsounds_english_consonant - 1;
						
						if (storyofsounds_english_view_consonant < 0) {
							storyofsounds_english_view_consonant = 0;
						}
						
						if (storyofsounds_english_view_consonant > storyofsounds_english_consonants.length - 6) {
							storyofsounds_english_view_consonant = storyofsounds_english_consonants.length - 6;
						}
						
						storyofsounds_english_play_syllable();
					}
					
					// Vowels
					for (i = 2; i < 32; i += 6) {
						context.beginPath();
						context.moveTo(i*canvas.width/32, 0);
						context.lineTo(i*canvas.width/32,133*canvas.height/180);
						context.lineWidth = canvas.height / 200;
						context.strokeStyle = "white";
						context.stroke();
						
						context.fillStyle = "cyan";
						context.font = "" + canvas.height/30 + "px monospace";
						
						context.fillText(storyofsounds_english_vowels[(i-2)/6+storyofsounds_english_view_vowel], (i+2.9)*canvas.width/32, canvas.height/20);
					}
					
					// Consonants
					for (i = 13; i < 130; i+= 20) {
						context.beginPath();
						context.moveTo(0, i*canvas.height/180);
						context.lineTo(canvas.width, i*canvas.height/180);
						context.lineWidth = canvas.height / 200;
						context.strokeStyle = "white";
						context.stroke();
						
						context.fillStyle = "cyan";
						context.font = "" + canvas.height/30 + "px monospace";
						
						context.fillText(storyofsounds_english_consonants[(i-13)/20+storyofsounds_english_view_consonant], canvas.width/200, (i+13)*canvas.height/180);
					}
					
					// Selected syllable
					context.beginPath();
					context.moveTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineTo((2+(storyofsounds_english_vowel-storyofsounds_english_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_english_consonant-storyofsounds_english_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					for (i = 0; i < 6; i++) {
						for (j = 0; j < 5; j++) {
							blue = 255;
							
							if (i + storyofsounds_english_view_consonant == storyofsounds_english_consonant) {
								blue = 170;
							}
							
							if (j + storyofsounds_english_view_vowel == storyofsounds_english_vowel) {
								if (blue == 170) {
									blue = 0;
								} else {
									blue = 170;
								}
							}
							
							context.fillStyle = "rgb(255,255," + blue + ")";
							context.font = "" + canvas.height/27 + "px monospace";
							
							context.fillText(storyofsounds_english_firsttableline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[i + storyofsounds_english_view_consonant] + storyofsounds_english_vowels[j + storyofsounds_english_view_vowel]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/20);
							
							context.fillText(storyofsounds_english_secondtableline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[i + storyofsounds_english_view_consonant] + storyofsounds_english_vowels[j + storyofsounds_english_view_vowel]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/11);
						}
					}
					
					context.fillStyle = "rgb(255,0,0)";
					context.font = "" + canvas.height/25 + "px monospace";
					
					context.fillText(storyofsounds_english_firstdownline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[storyofsounds_english_consonant] + storyofsounds_english_vowels[storyofsounds_english_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/20);
					context.fillText(storyofsounds_english_seconddownline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[storyofsounds_english_consonant] + storyofsounds_english_vowels[storyofsounds_english_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/10);
					context.fillText(storyofsounds_english_thirddownline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[storyofsounds_english_consonant] + storyofsounds_english_vowels[storyofsounds_english_vowel]],canvas.width/200,130*canvas.height/180 + 3*canvas.height/20);
					context.fillText(storyofsounds_english_fourthdownline[learning_languages[learning_language]]["" + storyofsounds_english_consonants[storyofsounds_english_consonant] + storyofsounds_english_vowels[storyofsounds_english_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/5);
				}
			} else if (learned_language == 1) { // toki pona
				//
				//
				// Story Of Sounds
				//
				//
				
				// Start
				if (toki_pona_freshly_chosen) {
					sound_to_play.pause();
					sound_to_play.currentTime = 0;
					
					//storyofsounds_toki_pona_play_syllable();
					toki_pona_freshly_chosen = false;
				}
				
				//
				// Actions
				//
				storyofsounds_speed = 1;
				
				if (key_pressed["Space"]) {
					storyofsounds_speed = 4;
				}
				
				if (key_freshly_pressed["ArrowLeft"]) {
					storyofsounds_toki_pona_second_consonant -= storyofsounds_speed;
					
					if (storyofsounds_toki_pona_second_consonant < 0) {
						storyofsounds_toki_pona_second_consonant = storyofsounds_toki_pona_second_consonants.length + storyofsounds_toki_pona_second_consonant;
					}
					
					storyofsounds_toki_pona_view_second_consonant = storyofsounds_toki_pona_second_consonant - 3;
					
					if (storyofsounds_toki_pona_view_second_consonant < 0) {
						storyofsounds_toki_pona_view_second_consonant = 0;
					}
					
					if (storyofsounds_toki_pona_view_second_consonant > storyofsounds_toki_pona_second_consonants.length - 5) {
						storyofsounds_toki_pona_view_second_consonant = storyofsounds_toki_pona_second_consonants.length - 5;
					}
					
					//storyofsounds_toki_pona_play_syllable();
				}
				
				if (key_freshly_pressed["ArrowRight"]) {
					storyofsounds_toki_pona_second_consonant += storyofsounds_speed;
					
					if (storyofsounds_toki_pona_second_consonant >= storyofsounds_toki_pona_second_consonants.length) {
						storyofsounds_toki_pona_second_consonant -= storyofsounds_toki_pona_second_consonants.length;
					}
					
					storyofsounds_toki_pona_view_second_consonant = storyofsounds_toki_pona_second_consonant - 1;
					
					if (storyofsounds_toki_pona_view_second_consonant < 0) {
						storyofsounds_toki_pona_view_second_consonant = 0;
					}
					
					if (storyofsounds_toki_pona_view_second_consonant > storyofsounds_toki_pona_second_consonants.length - 5) {
						storyofsounds_toki_pona_view_second_consonant = storyofsounds_toki_pona_second_consonants.length - 5;
					}
					
					//storyofsounds_toki_pona_play_syllable();
				}
				
				if (key_freshly_pressed["ArrowUp"]) {
					storyofsounds_toki_pona_first_consonant -= storyofsounds_speed;
					
					if (storyofsounds_toki_pona_first_consonant < 0) {
						storyofsounds_toki_pona_first_consonant = storyofsounds_toki_pona_first_consonants.length + storyofsounds_toki_pona_first_consonant;
					}
					
					storyofsounds_toki_pona_view_first_consonant = storyofsounds_toki_pona_first_consonant - 4;
					
					if (storyofsounds_toki_pona_view_first_consonant < 0) {
						storyofsounds_toki_pona_view_first_consonant = 0;
					}
					
					if (storyofsounds_toki_pona_view_first_consonant > storyofsounds_toki_pona_first_consonants.length - 6) {
						storyofsounds_toki_pona_view_first_consonant = storyofsounds_toki_pona_first_consonants.length - 6;
					}
					
					//storyofsounds_toki_pona_play_syllable();
				}
				
				if (key_freshly_pressed["ArrowDown"]) {
					storyofsounds_toki_pona_first_consonant += storyofsounds_speed;
					
					if (storyofsounds_toki_pona_first_consonant >= storyofsounds_toki_pona_first_consonants.length) {
						storyofsounds_toki_pona_first_consonant -= storyofsounds_toki_pona_first_consonants.length;
					}
					
					storyofsounds_toki_pona_view_first_consonant = storyofsounds_toki_pona_first_consonant - 1;
					
					if (storyofsounds_toki_pona_view_first_consonant < 0) {
						storyofsounds_toki_pona_view_first_consonant = 0;
					}
					
					if (storyofsounds_toki_pona_view_first_consonant > storyofsounds_toki_pona_first_consonants.length - 6) {
						storyofsounds_toki_pona_view_first_consonant = storyofsounds_toki_pona_first_consonants.length - 6;
					}
					
					//storyofsounds_toki_pona_play_syllable();
				}
				
				// Second consonant
				for (i = 2; i < 32; i += 6) {
					context.beginPath();
					context.moveTo(i*canvas.width/32, 0);
					context.lineTo(i*canvas.width/32,133*canvas.height/180);
					context.lineWidth = canvas.height / 200;
					context.strokeStyle = "white";
					context.stroke();
					
					context.fillStyle = "cyan";
					context.font = "" + canvas.height/30 + "px monospace";
					
					context.fillText(storyofsounds_toki_pona_second_consonants[(i-2)/6+storyofsounds_toki_pona_view_second_consonant], (i+2.9)*canvas.width/32, canvas.height/20);
				}
				
				// First consonant
				for (i = 13; i < 130; i+= 20) {
					context.beginPath();
					context.moveTo(0, i*canvas.height/180);
					context.lineTo(canvas.width, i*canvas.height/180);
					context.lineWidth = canvas.height / 200;
					context.strokeStyle = "white";
					context.stroke();
					
					context.fillStyle = "cyan";
					context.font = "" + canvas.height/30 + "px monospace";
					
					context.fillText(storyofsounds_toki_pona_first_consonants[(i-13)/20+storyofsounds_toki_pona_view_first_consonant], canvas.width/200, (i+13)*canvas.height/180);
				}
				
				// Selected syllable
				context.beginPath();
				context.moveTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180);
				context.lineTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180 + canvas.height/9);
				context.lineWidth = canvas.height / 100;
				context.strokeStyle = "yellow";
				context.stroke();
				
				context.beginPath();
				context.moveTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180);
				context.lineTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180 + canvas.height/9);
				context.lineWidth = canvas.height / 100;
				context.strokeStyle = "yellow";
				context.stroke();
				
				context.beginPath();
				context.moveTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180);
				context.lineTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180);
				context.lineWidth = canvas.height / 100;
				context.strokeStyle = "yellow";
				context.stroke();
				
				context.beginPath();
				context.moveTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180 + canvas.height/9);
				context.lineTo((2+(storyofsounds_toki_pona_second_consonant-storyofsounds_toki_pona_view_second_consonant)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_toki_pona_first_consonant-storyofsounds_toki_pona_view_first_consonant)*20)*canvas.height/180 + canvas.height/9);
				context.lineWidth = canvas.height / 100;
				context.strokeStyle = "yellow";
				context.stroke();
				
				for (i = 0; i < 6; i++) {
					for (j = 0; j < 5; j++) {
						blue = 255;
						
						if (i + storyofsounds_toki_pona_view_first_consonant == storyofsounds_toki_pona_first_consonant) {
							blue = 170;
						}
						
						if (j + storyofsounds_toki_pona_view_second_consonant == storyofsounds_toki_pona_second_consonant) {
							if (blue == 170) {
								blue = 0;
							} else {
								blue = 170;
							}
						}
						
						context.fillStyle = "rgb(255,255," + blue + ")";
						context.font = "" + canvas.height/27 + "px monospace";
						
						context.fillText(storyofsounds_toki_pona_firsttableline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[i + storyofsounds_toki_pona_view_first_consonant] + storyofsounds_toki_pona_second_consonants[j + storyofsounds_toki_pona_view_second_consonant]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/20);
						
						context.fillText(storyofsounds_toki_pona_secondtableline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[i + storyofsounds_toki_pona_view_first_consonant] + storyofsounds_toki_pona_second_consonants[j + storyofsounds_toki_pona_view_second_consonant]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/11);
					}
				}
				
				context.fillStyle = "rgb(255,0,0)";
				context.font = "" + canvas.height/25 + "px monospace";
				
				context.fillText(storyofsounds_toki_pona_firstdownline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[storyofsounds_toki_pona_first_consonant] + storyofsounds_toki_pona_second_consonants[storyofsounds_toki_pona_second_consonant]],canvas.width/200,130*canvas.height/180 + canvas.height/20);
				context.fillText(storyofsounds_toki_pona_seconddownline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[storyofsounds_toki_pona_first_consonant] + storyofsounds_toki_pona_second_consonants[storyofsounds_toki_pona_second_consonant]],canvas.width/200,130*canvas.height/180 + canvas.height/10);
				context.fillText(storyofsounds_toki_pona_thirddownline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[storyofsounds_toki_pona_first_consonant] + storyofsounds_toki_pona_second_consonants[storyofsounds_toki_pona_second_consonant]],canvas.width/200,130*canvas.height/180 + 3*canvas.height/20);
				context.fillText(storyofsounds_toki_pona_fourthdownline[learning_languages[learning_language]]["" + storyofsounds_toki_pona_first_consonants[storyofsounds_toki_pona_first_consonant] + storyofsounds_toki_pona_second_consonants[storyofsounds_toki_pona_second_consonant]],canvas.width/200,130*canvas.height/180 + canvas.height/5);
			} else if (learned_language == 2) { // zjlimpa
				if (storyofsounds_active == true) {
					//
					//
					// Story Of Sounds
					//
					//
					
					// Start
					if (zjlimpa_freshly_chosen) {
						sound_to_play.pause();
						sound_to_play.currentTime = 0;
						
						storyofsounds_zjlimpa_play_syllable();
						zjlimpa_freshly_chosen = false;
					}
					
					//
					// Actions
					//
					storyofsounds_speed = 1;
					
					if (key_pressed["Space"]) {
						storyofsounds_speed = 4;
					}
					
					if (key_freshly_pressed["Escape"]) {
						storyofsounds_active = false;
					}
					
					if (key_freshly_pressed["ArrowLeft"]) {
						storyofsounds_zjlimpa_vowel -= storyofsounds_speed;
						
						if (storyofsounds_zjlimpa_vowel < 0) {
							storyofsounds_zjlimpa_vowel = storyofsounds_zjlimpa_vowels.length + storyofsounds_zjlimpa_vowel;
						}
						
						storyofsounds_zjlimpa_view_vowel = storyofsounds_zjlimpa_vowel - 3;
						
						if (storyofsounds_zjlimpa_view_vowel < 0) {
							storyofsounds_zjlimpa_view_vowel = 0;
						}
						
						if (storyofsounds_zjlimpa_view_vowel > storyofsounds_zjlimpa_vowels.length - 5) {
							storyofsounds_zjlimpa_view_vowel = storyofsounds_zjlimpa_vowels.length - 5;
						}
						
						storyofsounds_zjlimpa_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowRight"]) {
						storyofsounds_zjlimpa_vowel += storyofsounds_speed;
						
						if (storyofsounds_zjlimpa_vowel >= storyofsounds_zjlimpa_vowels.length) {
							storyofsounds_zjlimpa_vowel -= storyofsounds_zjlimpa_vowels.length;
						}
						
						storyofsounds_zjlimpa_view_vowel = storyofsounds_zjlimpa_vowel - 1;
						
						if (storyofsounds_zjlimpa_view_vowel < 0) {
							storyofsounds_zjlimpa_view_vowel = 0;
						}
						
						if (storyofsounds_zjlimpa_view_vowel > storyofsounds_zjlimpa_vowels.length - 5) {
							storyofsounds_zjlimpa_view_vowel = storyofsounds_zjlimpa_vowels.length - 5;
						}
						
						storyofsounds_zjlimpa_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowUp"]) {
						storyofsounds_zjlimpa_consonant -= storyofsounds_speed;
						
						if (storyofsounds_zjlimpa_consonant < 0) {
							storyofsounds_zjlimpa_consonant = storyofsounds_zjlimpa_consonants.length + storyofsounds_zjlimpa_consonant;
						}
						
						storyofsounds_zjlimpa_view_consonant = storyofsounds_zjlimpa_consonant - 4;
						
						if (storyofsounds_zjlimpa_view_consonant < 0) {
							storyofsounds_zjlimpa_view_consonant = 0;
						}
						
						if (storyofsounds_zjlimpa_view_consonant > storyofsounds_zjlimpa_consonants.length - 6) {
							storyofsounds_zjlimpa_view_consonant = storyofsounds_zjlimpa_consonants.length - 6;
						}
						
						storyofsounds_zjlimpa_play_syllable();
					}
					
					if (key_freshly_pressed["ArrowDown"]) {
						storyofsounds_zjlimpa_consonant += storyofsounds_speed;
						
						if (storyofsounds_zjlimpa_consonant >= storyofsounds_zjlimpa_consonants.length) {
							storyofsounds_zjlimpa_consonant -= storyofsounds_zjlimpa_consonants.length;
						}
						
						storyofsounds_zjlimpa_view_consonant = storyofsounds_zjlimpa_consonant - 1;
						
						if (storyofsounds_zjlimpa_view_consonant < 0) {
							storyofsounds_zjlimpa_view_consonant = 0;
						}
						
						if (storyofsounds_zjlimpa_view_consonant > storyofsounds_zjlimpa_consonants.length - 6) {
							storyofsounds_zjlimpa_view_consonant = storyofsounds_zjlimpa_consonants.length - 6;
						}
						
						storyofsounds_zjlimpa_play_syllable();
					}
					
					// Vowels
					for (i = 2; i < 32; i += 6) {
						context.beginPath();
						context.moveTo(i*canvas.width/32, 0);
						context.lineTo(i*canvas.width/32,133*canvas.height/180);
						context.lineWidth = canvas.height / 200;
						context.strokeStyle = "white";
						context.stroke();
						
						context.fillStyle = "cyan";
						context.font = "" + canvas.height/30 + "px monospace";
						
						context.fillText(storyofsounds_zjlimpa_vowels[(i-2)/6+storyofsounds_zjlimpa_view_vowel], (i+2.9)*canvas.width/32, canvas.height/20);
					}
					
					// Consonants
					for (i = 13; i < 130; i+= 20) {
						context.beginPath();
						context.moveTo(0, i*canvas.height/180);
						context.lineTo(canvas.width, i*canvas.height/180);
						context.lineWidth = canvas.height / 200;
						context.strokeStyle = "white";
						context.stroke();
						
						context.fillStyle = "cyan";
						context.font = "" + canvas.height/30 + "px monospace";
						
						context.fillText(storyofsounds_zjlimpa_consonants[(i-13)/20+storyofsounds_zjlimpa_view_consonant], canvas.width/200, (i+13)*canvas.height/180);
					}
					
					// Selected syllable
					context.beginPath();
					context.moveTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180);
					context.lineTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					context.beginPath();
					context.moveTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineTo((2+(storyofsounds_zjlimpa_vowel-storyofsounds_zjlimpa_view_vowel)*6)*canvas.width/32 + 6*canvas.width/32, (13+(storyofsounds_zjlimpa_consonant-storyofsounds_zjlimpa_view_consonant)*20)*canvas.height/180 + canvas.height/9);
					context.lineWidth = canvas.height / 100;
					context.strokeStyle = "yellow";
					context.stroke();
					
					for (i = 0; i < 6; i++) {
						for (j = 0; j < 5; j++) {
							blue = 255;
							
							if (i + storyofsounds_zjlimpa_view_consonant == storyofsounds_zjlimpa_consonant) {
								blue = 170;
							}
							
							if (j + storyofsounds_zjlimpa_view_vowel == storyofsounds_zjlimpa_vowel) {
								if (blue == 170) {
									blue = 0;
								} else {
									blue = 170;
								}
							}
							
							context.fillStyle = "rgb(255,255," + blue + ")";
							context.font = "" + canvas.height/27 + "px monospace";
							
							context.fillText(storyofsounds_zjlimpa_firsttableline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[i + storyofsounds_zjlimpa_view_consonant] + storyofsounds_zjlimpa_vowels[j + storyofsounds_zjlimpa_view_vowel]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/20);
							
							context.fillText(storyofsounds_zjlimpa_secondtableline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[i + storyofsounds_zjlimpa_view_consonant] + storyofsounds_zjlimpa_vowels[j + storyofsounds_zjlimpa_view_vowel]], (2+j*6)*canvas.width/32 + canvas.width/100, (13+i*20)*canvas.height/180 + canvas.height/11);
						}
					}
					
					context.fillStyle = "rgb(255,0,0)";
					context.font = "" + canvas.height/25 + "px monospace";
					
					context.fillText(storyofsounds_zjlimpa_firstdownline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[storyofsounds_zjlimpa_consonant] + storyofsounds_zjlimpa_vowels[storyofsounds_zjlimpa_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/20);
					context.fillText(storyofsounds_zjlimpa_seconddownline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[storyofsounds_zjlimpa_consonant] + storyofsounds_zjlimpa_vowels[storyofsounds_zjlimpa_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/10);
					context.fillText(storyofsounds_zjlimpa_thirddownline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[storyofsounds_zjlimpa_consonant] + storyofsounds_zjlimpa_vowels[storyofsounds_zjlimpa_vowel]],canvas.width/200,130*canvas.height/180 + 3*canvas.height/20);
					context.fillText(storyofsounds_zjlimpa_fourthdownline[learning_languages[learning_language]]["" + storyofsounds_zjlimpa_consonants[storyofsounds_zjlimpa_consonant] + storyofsounds_zjlimpa_vowels[storyofsounds_zjlimpa_vowel]],canvas.width/200,130*canvas.height/180 + canvas.height/5);
				} else {
					while (storyoftheprimecity_i < storyoftheprimecity_selected_number + 1000) {
						limit = Math.sqrt(storyoftheprimecity_i) + 1;
						
						real_i = storyoftheprimecity_i;
						power = 0;
						
						storyoftheprimecity_factorization[storyoftheprimecity_i] = "";
						
						while (real_i % 2 == 0) {
							real_i /= 2;
							power++;
						}
						
						if (power > 1) {
							storyoftheprimecity_factorization[storyoftheprimecity_i] += "2 ^ " + power;
						} else if (power == 1) {
							storyoftheprimecity_factorization[storyoftheprimecity_i] += "" + "2";
						}
						
						real_i_two = real_i;
						
						storyoftheprimecity_window[storyoftheprimecity_i] = true;
						storyoftheprimecity_door[storyoftheprimecity_i] = false;
						
						for (i = 3; i < limit; i += 2) {
							power = 0;
							
							while (real_i % i == 0) {
								real_i /= i;
								power++;
								
								if (real_i > 1) {
									storyoftheprimecity_window[storyoftheprimecity_i] = false;
								}
							}
							
							if (power > 0) {
								if (storyoftheprimecity_factorization[storyoftheprimecity_i].length > 0) {
									storyoftheprimecity_factorization[storyoftheprimecity_i] += " * ";
								}
								
								storyoftheprimecity_factorization[storyoftheprimecity_i] += "" + i;
								
								if (power > 1) {
									storyoftheprimecity_factorization[storyoftheprimecity_i] += " ^ " + power;
								}
							}
							
							if (real_i == 1) {
								i = limit;
							}
						}
						
						
						max = 0;
						maxpos = 0;
						
						
						if (storyoftheprimecity_factorization[storyoftheprimecity_i].length == 0 || storyoftheprimecity_i == 2) {
							storyoftheprimecity_factorization[storyoftheprimecity_i] = "Sadj!";
							
							if (storyoftheprimecity_i > 10) {
								if (storyoftheprimecity_factorization[storyoftheprimecity_i - 2] == "Sadj!" && storyoftheprimecity_factorization[storyoftheprimecity_i] == "Sadj!") {

								
									j = 4; 
									stop = false
									//while (storyoftheprimecity_factorization[storyoftheprimecity_i - j] != "Sadj!" && storyoftheprimecity_factorization[storyoftheprimecity_i - j - 2] != "Sadj!") {
									while (!stop) {
										storyoftheprimecity_hue[storyoftheprimecity_i - j] = Math.floor(((Math.log(storyoftheprimecity_i) / Math.log(1.5)) % 1) * 360);
										console.log("" + (storyoftheprimecity_i - j) + " : " + storyoftheprimecity_hue[storyoftheprimecity_i - j]);
										storyoftheprimecity_hue[storyoftheprimecity_i - j - 1] = storyoftheprimecity_hue[storyoftheprimecity_i - j];
										
										if (storyoftheprimecity_factorization[storyoftheprimecity_i - j + 2] == "Sadj!") {
											if (storyoftheprimecity_factorization[storyoftheprimecity_i - j] == "Sadj!") {
												stop = true;
											} else {
												
												temp_number = storyoftheprimecity_factorization[storyoftheprimecity_i - j].match(/^\d+|\d+\b|\d+(?=\w)/g || []).map(function (v) {return +v;})[0];
												
												//console.log(temp_number);
												
												if (0+temp_number > max) {
													max = 0+temp_number;
													maxpos = j;
												}
											}
										}
										
										//console.log("" + storyoftheprimecity_i + " - " + j);
										
										j += 2;
										
									}
								}
								
								storyoftheprimecity_door[storyoftheprimecity_i - maxpos] = true;
							}
						}
						
						storyoftheprimecity_i++;
					}
					
					//
					// Draw the hero
					//
					/*
					// Draw the block
					fill = "rgb(" + hero_background_color_red + "," + hero_background_color_green + "," + hero_background_color_blue + ")";
					context.fillStyle = fill;
					context.fillRect(canvas.width/2,canvas.height/2,canvas.width/32,canvas.height/18);
					
					// Draw the text
					// Get the letter
					letter = hero_name_in_pieces[Math.floor(miliseconds / hero_letter_change) % hero_name_in_pieces.length];
					
					if (getRandomInt(1,3) == 1) {
						context.fillStyle = "white";
					} else {
						context.fillStyle = "rgb(" + hero_text_color_red + "," + hero_text_color_green + "," + hero_text_color_blue + ")";
					}
					
					context.font = "" + canvas.height/30 + "px monospace";
					context.fillText(letter, canvas.width/2 + canvas.width/123, canvas.height/2 + canvas.height/30);*/
					
					context.fillStyle = "cyan";
					context.fillRect(0,0,canvas.width,canvas.height);
					
					context.fillStyle = "black";
					context.font = "" + canvas.height/10 + "px monospace";
					context.fillText("" + storyoftheprimecity_selected_number + " : " + storyoftheprimecity_factorization[storyoftheprimecity_selected_number], canvas.height/100, canvas.height/100+canvas.height/10);
					
					k = storyoftheprimecity_x;
					
					
					
					k2 = k;
					
					for (i = 0; i < 32; i++) {
						j = k2;
						
						while (storyoftheprimecity_factorization[j] != "Sadj!") {
							j++;
						}
						
						
						
						for (l = k2; l <= j; l++) {
							if (l == j) {
								context.fillStyle = "brown";
								context.fillRect(i*canvas.width/32, 16*canvas.height/18, canvas.width/32, canvas.height/18);
							} else if (l == j - 1) {
								context.fillStyle = "black";
								context.fillRect(i*canvas.width/32, 15*canvas.height/18, canvas.width/32, canvas.height/18);
								
								context.strokeStyle = "white";
								context.lineWidth = canvas.height / 300;
								context.beginPath();
								context.moveTo(i*canvas.width/32 + canvas.width/192, 15.5*canvas.height/18);
								context.lineTo((i+1)*canvas.width/32 - canvas.width/192, 15.5*canvas.height/18);
								context.stroke();
							} else {
								context.fillStyle = "hsl(" + storyoftheprimecity_hue[l] + ",100%,75%)";
								context.fillRect(i*canvas.width/32, (16 + l - j)*canvas.height/18, canvas.width/32, canvas.height/18);
								
								/*context.fillStyle = "black";
								context.font = "10px monospace";
								context.fillText(storyoftheprimecity_door[l], i*canvas.width/32, (17 + l - j)*canvas.height/18);*/
								
								if (storyoftheprimecity_window[l] == true && storyoftheprimecity_door[l+1] == false) {
									context.fillStyle = "rgb(100,100,100)";
									
									context.fillRect(i*canvas.width/32 + canvas.width/150, (16 + l - j)*canvas.height/18 + canvas.height/100, canvas.width/70, canvas.height/30);
								} else if (storyoftheprimecity_door[l] == true) {
									context.fillStyle = "orange";
									
									context.fillRect(i*canvas.width/32 + canvas.width/150, (15 + l - j)*canvas.height/18 + canvas.height/40, canvas.width/50, canvas.height/9-canvas.height/40);
									
									context.strokeStyle = "black";
									context.lineWidth = canvas.height / 500;
									context.beginPath();
									context.moveTo(i*canvas.width/32 + canvas.width/50, (15 + l - j)*canvas.height/18 + canvas.height/55 + canvas.height/18);
									context.lineTo(i*canvas.width/32 + canvas.width/40, (15 + l - j)*canvas.height/18 + canvas.height/55 + canvas.height/19);
									context.stroke();
								}
							}
						}
						
						k2 = l;
					}
				}
			} else if (learned_language == 3) { // Türkçe
				if (turkish_right_pressed == false) {
					if (key_pressed["Space"] == true) {
						turkish_random_number = getRandomInt(0, 9999);
						turkish_random_number_all_in_letters = turkish_get_number_all_in_letters(turkish_random_number);
					}
				
					context.fillStyle = "white";
					context.font = "" + canvas.height/3 + "px monospace";
					
					context.fillText(turkish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
					
					context.font = "" + canvas.height/20 + "px monospace";
					
					context.fillText(turkish_random_number_all_in_letters, canvas.width/50, canvas.height/2 + canvas.height/20);
					
					if (key_pressed["ArrowRight"]) {
						turkish_right_pressed = true;
						turkish_random_number = getRandomInt(0, 9999);
						turkish_random_number_all_in_letters = turkish_get_number_all_in_letters(turkish_random_number);
					}
				} else {
					if (turkish_random_number_solution == false) {
						// Middle range
						if (key_freshly_pressed["KeyA"]) {
							turkish_random_number_exercise += "a";
						} else if (key_freshly_pressed["KeyS"]) {
							turkish_random_number_exercise += "ı";
						} else if (key_freshly_pressed["KeyD"]) {
							turkish_random_number_exercise += "o";
						} else if (key_freshly_pressed["KeyF"]) {
							turkish_random_number_exercise += "u";
						} else if (key_freshly_pressed["KeyG"]) {
							turkish_random_number_exercise += "p";
						} else if (key_freshly_pressed["KeyH"]) {
							turkish_random_number_exercise += "b";
						} else if (key_freshly_pressed["KeyJ"]) {
							turkish_random_number_exercise += "t";
						} else if (key_freshly_pressed["KeyK"]) {
							turkish_random_number_exercise += "d";
						} else if (key_freshly_pressed["KeyL"]) {
							turkish_random_number_exercise += "ç";
						} else if (key_freshly_pressed["Semicolon"]) {
							turkish_random_number_exercise += "c";
						} else if (key_freshly_pressed["Quote"]) {
							turkish_random_number_exercise += "k";
						// High range
						} else if (key_freshly_pressed["KeyQ"]) {
							turkish_random_number_exercise += "e";
						} else if (key_freshly_pressed["KeyW"]) {
							turkish_random_number_exercise += "i";
						} else if (key_freshly_pressed["KeyE"]) {
							turkish_random_number_exercise += "ö";
						} else if (key_freshly_pressed["KeyR"]) {
							turkish_random_number_exercise += "ü";
						} else if (key_freshly_pressed["KeyT"]) {
							turkish_random_number_exercise += "m";
						} else if (key_freshly_pressed["KeyY"]) {
							turkish_random_number_exercise += "n";
						} else if (key_freshly_pressed["KeyU"]) {
							turkish_random_number_exercise += "l";
						} else if (key_freshly_pressed["KeyI"]) {
							turkish_random_number_exercise += "r";
						} else if (key_freshly_pressed["KeyO"]) {
							turkish_random_number_exercise += "y";
						} else if (key_freshly_pressed["KeyP"]) {
							turkish_random_number_exercise += "g";
						} else if (key_freshly_pressed["BracketLeft"]) {
							turkish_random_number_exercise += "ğ";
						// Low range
						} else if (key_freshly_pressed["Slash"]) {
							turkish_random_number_exercise += "f";
						} else if (key_freshly_pressed["Period"]) {
							turkish_random_number_exercise += "v";
						} else if (key_freshly_pressed["Comma"]) {
							turkish_random_number_exercise += "s";
						} else if (key_freshly_pressed["KeyM"]) {
							turkish_random_number_exercise += "z";
						} else if (key_freshly_pressed["KeyN"]) {
							turkish_random_number_exercise += "ş";
						} else if (key_freshly_pressed["KeyB"]) {
							turkish_random_number_exercise += "h";
						} else if (key_freshly_pressed["KeyV"]) {
							turkish_random_number_exercise += "ń";
						// Space
						} else if (key_freshly_pressed["Space"]) {
							turkish_random_number_exercise += " ";
						} else if (key_freshly_pressed["Backspace"]) {
							turkish_random_number_exercise = turkish_random_number_exercise.substr(0, turkish_random_number_exercise.length - 1);
						// Enter
						} else if (key_freshly_pressed["Enter"]) {
							turkish_random_number_solution = true;
						}
						if (miliseconds % 2000 < 1000) {
							turkish_random_number_exercise_showing = turkish_random_number_exercise + "|";
						} else {
							turkish_random_number_exercise_showing = turkish_random_number_exercise;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(turkish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(turkish_random_number_exercise_showing, canvas.width/50, canvas.height/2 + canvas.height/20);
					} else {
						if (key_freshly_pressed["Enter"]) {
							turkish_random_number = getRandomInt(0, 9999);
							turkish_random_number_all_in_letters = turkish_get_number_all_in_letters(turkish_random_number);
							turkish_random_number_exercise = "";
							turkish_random_number_solution = false;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(turkish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
						
						
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(turkish_random_number_all_in_letters, canvas.width/50, 2*canvas.height/3 + canvas.height/20);
						
						if (turkish_random_number_all_in_letters == turkish_random_number_exercise) {
							context.fillStyle = "green";
						} else {
							context.fillStyle = "red";
						}
						
						context.fillText(turkish_random_number_exercise, canvas.width/50, canvas.height/2 + canvas.height/20);
						
						
					}
				}
			} else if (learned_language == 4) { // Polski
				if (polish_right_pressed == false) {
					if (key_pressed["Space"] == true) {
						polish_random_number = getRandomInt(0, 9999);
						polish_random_number_all_in_letters = polish_get_number_all_in_letters(polish_random_number);
					}
				
					context.fillStyle = "white";
					context.font = "" + canvas.height/3 + "px monospace";
					
					context.fillText(polish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
					
					context.font = "" + canvas.height/20 + "px monospace";
					
					context.fillText(polish_random_number_all_in_letters, canvas.width/50, canvas.height/2 + canvas.height/20);
					
					if (key_pressed["ArrowRight"]) {
						polish_right_pressed = true;
						polish_random_number = getRandomInt(0, 9999);
						polish_random_number_all_in_letters = polish_get_number_all_in_letters(polish_random_number);
					}
				} else {
					if (polish_random_number_solution == false) {
						// Middle range
						if (key_freshly_pressed["KeyA"]) {
							polish_random_number_exercise += "i";
						} else if (key_freshly_pressed["KeyS"]) {
							polish_random_number_exercise += "e";
						} else if (key_freshly_pressed["KeyD"]) {
							polish_random_number_exercise += "a";
						} else if (key_freshly_pressed["KeyF"]) {
							polish_random_number_exercise += "o";
						} else if (key_freshly_pressed["KeyG"]) {
							polish_random_number_exercise += "p";
						} else if (key_freshly_pressed["KeyH"]) {
							polish_random_number_exercise += "b";
						} else if (key_freshly_pressed["KeyJ"]) {
							polish_random_number_exercise += "t";
						} else if (key_freshly_pressed["KeyK"]) {
							polish_random_number_exercise += "d";
						} else if (key_freshly_pressed["KeyL"]) {
							polish_random_number_exercise += "c";
						} else if (key_freshly_pressed["Semicolon"]) {
							polish_random_number_exercise += "ć";
						} else if (key_freshly_pressed["Quote"]) {
							polish_random_number_exercise += "k";
						// High range
						} else if (key_freshly_pressed["KeyQ"]) {
							polish_random_number_exercise += "ę";
						} else if (key_freshly_pressed["KeyW"]) {
							polish_random_number_exercise += "y";
						} else if (key_freshly_pressed["KeyE"]) {
							polish_random_number_exercise += "ą";
						} else if (key_freshly_pressed["KeyR"]) {
							if (key_pressed["AltRight"]) {
								polish_random_number_exercise += "ó";
							} else {
								polish_random_number_exercise += "u";
							}
						} else if (key_freshly_pressed["KeyT"]) {
							polish_random_number_exercise += "m";
						} else if (key_freshly_pressed["KeyY"]) {
							polish_random_number_exercise += "n";
						} else if (key_freshly_pressed["KeyU"]) {
							polish_random_number_exercise += "l";
						} else if (key_freshly_pressed["KeyI"]) {
							polish_random_number_exercise += "r";
						} else if (key_freshly_pressed["KeyO"]) {
							polish_random_number_exercise += "j";
						} else if (key_freshly_pressed["KeyP"]) {
							polish_random_number_exercise += "g";
						} else if (key_freshly_pressed["BracketLeft"]) {
							polish_random_number_exercise += "h";
						// Low range
						} else if (key_freshly_pressed["Slash"]) {
							polish_random_number_exercise += "f";
						} else if (key_freshly_pressed["Period"]) {
							polish_random_number_exercise += "w";
						} else if (key_freshly_pressed["Comma"]) {
							polish_random_number_exercise += "s";
						} else if (key_freshly_pressed["KeyM"]) {
							polish_random_number_exercise += "z";
						} else if (key_freshly_pressed["KeyN"]) {
							polish_random_number_exercise += "ż";
						} else if (key_freshly_pressed["KeyB"]) {
							polish_random_number_exercise += "ś";
						} else if (key_freshly_pressed["KeyV"]) {
							polish_random_number_exercise += "ź";
						// Space
						} else if (key_freshly_pressed["Space"]) {
							polish_random_number_exercise += " ";
						} else if (key_freshly_pressed["Backspace"]) {
							polish_random_number_exercise = polish_random_number_exercise.substr(0, polish_random_number_exercise.length - 1);
						// Enter
						} else if (key_freshly_pressed["Enter"]) {
							polish_random_number_solution = true;
						}
						if (miliseconds % 2000 < 1000) {
							polish_random_number_exercise_showing = polish_random_number_exercise + "|";
						} else {
							polish_random_number_exercise_showing = polish_random_number_exercise;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(polish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(polish_random_number_exercise_showing, canvas.width/50, canvas.height/2 + canvas.height/20);
					} else {
						if (key_freshly_pressed["Enter"]) {
							polish_random_number = getRandomInt(0, 9999);
							polish_random_number_all_in_letters = polish_get_number_all_in_letters(polish_random_number);
							polish_random_number_exercise = "";
							polish_random_number_solution = false;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(polish_random_number, canvas.width/50, canvas.width/50 + canvas.height/3);
						
						
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(polish_random_number_all_in_letters, canvas.width/50, 2*canvas.height/3 + canvas.height/20);
						
						if (polish_random_number_all_in_letters == polish_random_number_exercise) {
							context.fillStyle = "green";
						} else {
							context.fillStyle = "red";
						}
						
						context.fillText(polish_random_number_exercise, canvas.width/50, canvas.height/2 + canvas.height/20);
						
						
					}
				}
			} else if (learned_language == 5) { //日本語
				if (japanese_right_pressed == false) {
					if (key_pressed["Space"] == true) {
						japanese_kana_random = getRandomInt(0, japanese_kana_characters.length - 1);
					}
				
					context.fillStyle = "white";
					context.font = "" + canvas.height/3 + "px monospace";
					
					context.fillText(japanese_kana_characters[japanese_kana_random], canvas.width/50, canvas.width/50 + canvas.height/3);
					
					context.font = "" + canvas.height/20 + "px monospace";
					
					context.fillText(japanese_kana_readings[japanese_kana_random], canvas.width/50, canvas.height/2 + canvas.height/20);
					context.fillText(japanese_kana_name[learning_languages[learning_language]][japanese_kana_hiragana[japanese_kana_random]], canvas.width/50 + canvas.width / 2, canvas.height/2 + canvas.height/20);
					
					if (key_pressed["ArrowRight"]) {
						japanese_right_pressed = true;
						japanese_kana_random = getRandomInt(0, japanese_kana_characters.length - 1);
					}
				} else {
					if (japanese_kana_solution == false) {
						// Middle range
						if (key_freshly_pressed["KeyA"]) {
							japanese_kana_exercise += "a";
						} else if (key_freshly_pressed["KeyS"]) {
							japanese_kana_exercise += "o";
						} else if (key_freshly_pressed["KeyD"]) {
							japanese_kana_exercise += "e";
						} else if (key_freshly_pressed["KeyF"]) {
							japanese_kana_exercise += "u";
						} else if (key_freshly_pressed["KeyG"]) {
							japanese_kana_exercise += "i";
						} else if (key_freshly_pressed["KeyH"]) {
							japanese_kana_exercise += "d";
						} else if (key_freshly_pressed["KeyJ"]) {
							japanese_kana_exercise += "h";
						} else if (key_freshly_pressed["KeyK"]) {
							japanese_kana_exercise += "t";
						} else if (key_freshly_pressed["KeyL"]) {
							japanese_kana_exercise += "n";
						} else if (key_freshly_pressed["Semicolon"]) {
							japanese_kana_exercise += "s";
						// High range
						} else if (key_freshly_pressed["KeyR"]) {
							japanese_kana_exercise += "p";
						} else if (key_freshly_pressed["KeyT"]) {
							japanese_kana_exercise += "y";
						} else if (key_freshly_pressed["KeyY"]) {
							japanese_kana_exercise += "f";
						} else if (key_freshly_pressed["KeyU"]) {
							japanese_kana_exercise += "g";
						} else if (key_freshly_pressed["KeyI"]) {
							japanese_kana_exercise += "c";
						} else if (key_freshly_pressed["KeyO"]) {
							japanese_kana_exercise += "r";
						} else if (key_freshly_pressed["KeyP"]) {
							japanese_kana_exercise += "l";
						// Low range
						} else if (key_freshly_pressed["KeyX"]) {
							japanese_kana_exercise += "q";
						} else if (key_freshly_pressed["KeyC"]) {
							japanese_kana_exercise += "j";
						} else if (key_freshly_pressed["KeyV"]) {
							japanese_kana_exercise += "k";
						} else if (key_freshly_pressed["KeyB"]) {
							japanese_kana_exercise += "x";
						} else if (key_freshly_pressed["KeyN"]) {
							japanese_kana_exercise += "b";
						} else if (key_freshly_pressed["KeyM"]) {
							japanese_kana_exercise += "m";
						} else if (key_freshly_pressed["Comma"]) {
							japanese_kana_exercise += "w";
						} else if (key_freshly_pressed["Period"]) {
							japanese_kana_exercise += "v";
						} else if (key_freshly_pressed["Slash"]) {
							japanese_kana_exercise += "z";
						// Space
						} else if (key_freshly_pressed["Space"]) {
							japanese_kana_exercise += " ";
						} else if (key_freshly_pressed["Backspace"]) {
							japanese_kana_exercise = japanese_kana_exercise.substr(0, japanese_kana_exercise.length - 1);
						// Enter
						} else if (key_freshly_pressed["Enter"]) {
							japanese_kana_solution = true;
						// Arrows
						} else if (key_freshly_pressed["ArrowUp"] || key_freshly_pressed["ArrowDown"]) {
							japanese_kana_exercise_set = getRandomInt(0, 2);
						}
						
						if (miliseconds % 2000 < 1000) {
							japanese_kana_exercise_showing = japanese_kana_exercise + "|";
						} else {
							japanese_kana_exercise_showing = japanese_kana_exercise;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(japanese_kana_characters[japanese_kana_random], canvas.width/50, canvas.width/50 + canvas.height/3);
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(japanese_kana_name[learning_languages[learning_language]][japanese_kana_exercise_set], canvas.width/50 + canvas.width/2, canvas.height/2 + canvas.height/20);
						context.fillText(japanese_kana_exercise_showing, canvas.width/50, canvas.height/2 + canvas.height/20);
					} else {
						if (key_freshly_pressed["Enter"]) {
							japanese_kana_random = getRandomInt(0, japanese_kana_characters.length - 1);
							japanese_kana_exercise = "";
							japanese_kana_solution = false;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(japanese_kana_characters[japanese_kana_random], canvas.width/50, canvas.width/50 + canvas.height/3);
						
						
						
						context.font = "" + canvas.height/20 + "px monospace";
						
						context.fillText(japanese_kana_name[learning_languages[learning_language]][japanese_kana_hiragana[japanese_kana_random]], canvas.width/50 + canvas.width/2, 2*canvas.height/3 + canvas.height/20);
						context.fillText(japanese_kana_readings[japanese_kana_random], canvas.width/50, 2*canvas.height/3 + canvas.height/20);
						
						if (japanese_kana_readings[japanese_kana_random] == japanese_kana_exercise && japanese_kana_hiragana[japanese_kana_random] == japanese_kana_exercise_set) {
							context.fillStyle = "green";
						} else {
							context.fillStyle = "red";
						}
						
						context.fillText(japanese_kana_name[learning_languages[learning_language]][japanese_kana_exercise_set], canvas.width/50 + canvas.width/2, canvas.height/2 + canvas.height/20);
						context.fillText(japanese_kana_exercise, canvas.width/50, canvas.height/2 + canvas.height/20);
						
						
					}
				}
			} else if (learned_language == 6) { //ᏣᎳᎩ
				if (cherokee_right_pressed == false) {
					if (key_pressed["Space"] == true) {
						cherokee_random = getRandomInt(0, cherokee_characters.length - 1);
					}
				
					context.fillStyle = "white";
					context.font = "" + canvas.height/3 + "px monospace";
					
					context.fillText(cherokee_characters[cherokee_random], canvas.width/50, canvas.width/50 + canvas.height/3);
					
					context.font = "" + canvas.height/20 + "px monospace";
					
					context.fillText(cherokee_readings[cherokee_random], canvas.width/50, canvas.height/2 + canvas.height/20);
					
					if (key_pressed["ArrowRight"]) {
						cherokee_right_pressed = true;
						cherokee_random = getRandomInt(0, japanese_kana_characters.length - 1);
					}
				} else {
					if (cherokee_solution == false) {
						// Middle range
						if (key_freshly_pressed["KeyA"]) {
							cherokee_exercise += "a";
						} else if (key_freshly_pressed["KeyS"]) {
							cherokee_exercise += "o";
						} else if (key_freshly_pressed["KeyD"]) {
							cherokee_exercise += "e";
						} else if (key_freshly_pressed["KeyF"]) {
							cherokee_exercise += "u";
						} else if (key_freshly_pressed["KeyG"]) {
							cherokee_exercise += "i";
						} else if (key_freshly_pressed["KeyH"]) {
							cherokee_exercise += "d";
						} else if (key_freshly_pressed["KeyJ"]) {
							cherokee_exercise += "h";
						} else if (key_freshly_pressed["KeyK"]) {
							cherokee_exercise += "t";
						} else if (key_freshly_pressed["KeyL"]) {
							cherokee_exercise += "n";
						} else if (key_freshly_pressed["Semicolon"]) {
							cherokee_exercise += "s";
						// High range
						} else if (key_freshly_pressed["KeyR"]) {
							cherokee_exercise += "p";
						} else if (key_freshly_pressed["KeyT"]) {
							cherokee_exercise += "y";
						} else if (key_freshly_pressed["KeyY"]) {
							cherokee_exercise += "f";
						} else if (key_freshly_pressed["KeyU"]) {
							cherokee_exercise += "g";
						} else if (key_freshly_pressed["KeyI"]) {
							cherokee_exercise += "c";
						} else if (key_freshly_pressed["KeyO"]) {
							cherokee_exercise += "r";
						} else if (key_freshly_pressed["KeyP"]) {
							cherokee_exercise += "l";
						// Low range
						} else if (key_freshly_pressed["KeyX"]) {
							cherokee_exercise += "q";
						} else if (key_freshly_pressed["KeyC"]) {
							cherokee_exercise += "j";
						} else if (key_freshly_pressed["KeyV"]) {
							cherokee_exercise += "k";
						} else if (key_freshly_pressed["KeyB"]) {
							cherokee_exercise += "x";
						} else if (key_freshly_pressed["KeyN"]) {
							cherokee_exercise += "b";
						} else if (key_freshly_pressed["KeyM"]) {
							cherokee_exercise += "m";
						} else if (key_freshly_pressed["Comma"]) {
							cherokee_exercise += "w";
						} else if (key_freshly_pressed["Period"]) {
							cherokee_exercise += "v";
						} else if (key_freshly_pressed["Slash"]) {
							cherokee_exercise += "z";
						// Space
						} else if (key_freshly_pressed["Space"]) {
							cherokee_exercise += " ";
						} else if (key_freshly_pressed["Backspace"]) {
							cherokee_exercise = cherokee_exercise.substr(0, cherokee_exercise.length - 1);
						// Enter
						} else if (key_freshly_pressed["Enter"]) {
							cherokee_solution = true;
						}
						
						if (miliseconds % 2000 < 1000) {
							cherokee_exercise_showing = cherokee_exercise + "|";
						} else {
							cherokee_exercise_showing = cherokee_exercise;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(cherokee_characters[cherokee_random], canvas.width/50, canvas.width/50 + canvas.height/3);
						
						context.font = "" + canvas.height/20 + "px monospace";
					
						context.fillText(cherokee_exercise_showing, canvas.width/50, canvas.height/2 + canvas.height/20);
					} else {
						if (key_freshly_pressed["Enter"]) {
							cherokee_random = getRandomInt(0, cherokee_characters.length - 1);
							cherokee_exercise = "";
							cherokee_solution = false;
						}
						
						context.fillStyle = "white";
						context.font = "" + canvas.height/3 + "px monospace";
						
						context.fillText(cherokee_characters[cherokee_random], canvas.width/50, canvas.width/50 + canvas.height/3);
						
						
						
						context.font = "" + canvas.height/20 + "px monospace";
						context.fillText(cherokee_readings[cherokee_random], canvas.width/50, 2*canvas.height/3 + canvas.height/20);
						
						if (cherokee_readings[cherokee_random] == cherokee_exercise) {
							context.fillStyle = "green";
						} else {
							context.fillStyle = "red";
						}
						
						context.fillText(cherokee_exercise, canvas.width/50, canvas.height/2 + canvas.height/20);
						
						
					}
				}
			} else if (learned_language == 10) { //Computer: TAD-1001
				if (computer_freshly_chosen == true) {
					/*
					Changes from 1.2.0:
					- Cyrillic now supported!
					*/
					
					dogline_message[dogline_message.length] = ("Welcome to TAD-1001 1.2.1 !").split("");
					dogline_speed[dogline_speed.length] = 25;
					backspeed = false;
					computer_freshly_chosen = false;
					
					computer_bigtext_set();
				}
			
				// Top range
				if (key_freshly_pressed["KeyQ"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["IntlBackslash"]) {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("¦");
								} else {
									computer_text_add_character("↑");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("B");
								} else {
									computer_text_add_character("↓");
								}
							}
						} else {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("¦");
								} else {
									computer_text_add_character("|");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("B");
								} else {
									computer_text_add_character("b");
								}
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("e");
						computer_text_add_character("n");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyW"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["AltRight"]) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_double_acute = true;
							} else {
								computer_text_acute = true;
							}
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("É");
							} else {
								computer_text_add_character("é");
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("p");
						computer_text_add_character("i");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyE"]) {
					if (key_pressed["ControlLeft"] || key_pressed["ControlRight"]) {
						if (key_pressed["CapsLock"]) {
							computer_text_keyboard = 3; // Emoji
							computer_text_tech_keyboard = false;
						}
					} else if (computer_text_keyboard == 0) {
						if (key_pressed["AltRight"]) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("§");
							} else {
								computer_text_add_character("&");
							}
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("P");
							} else {
								computer_text_add_character("p");
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("k");
						computer_text_add_character("e");
						computer_text_add_character("p");
						computer_text_add_character("e");
						computer_text_add_character("k");
						computer_text_add_character("e");
						computer_text_add_character("n");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyR"]) {
					if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Ô");
							} else {
								computer_text_add_character("ô");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["IntlBackslash"]) {
								if (key_pressed["AltRight"]) {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("¦");
									} else {
										computer_text_add_character("↑");
									}
								} else {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("Ơ");
									} else {
										computer_text_add_character("ơ");
									}
								}
							} else {
								if (key_pressed["AltRight"]) {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("Œ");
									} else {
										computer_text_add_character("œ");
									}
								} else {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("O");
									} else {
										computer_text_add_character("o");
									}
								}
							}

						}
					}
				} else if (key_freshly_pressed["KeyT"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("È");
						} else {
							computer_text_add_character("è");
						}
					}
				} else if (key_freshly_pressed["KeyY"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("!");
						} else {
							computer_text_circumflex = true;
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Б");
						} else {
							computer_text_add_character("б");
						}
					}
				} else if (key_freshly_pressed["KeyU"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("V");
						} else {
							computer_text_add_character("v");
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("П");
						} else {
							computer_text_add_character("п");
						}
					}
				} else if (key_freshly_pressed["KeyI"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("D");
						} else {
							computer_text_add_character("d");
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("К");
						} else {
							computer_text_add_character("к");
						}
					}
				} else if (key_freshly_pressed["KeyO"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("L");
						} else {
							computer_text_add_character("l");
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["AltRight"]) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Ц");
							} else {
								computer_text_add_character("ц");
							}
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Ч");
							} else {
								computer_text_add_character("ч");
							}
						}
					}
				} else if (key_freshly_pressed["KeyP"]) {
					if (key_pressed["ControlLeft"] || key_pressed["ControlRight"]) {
						if (key_pressed["CapsLock"]) {
							computer_text_keyboard = 2; // Toki Pona
							computer_text_tech_keyboard = false;
						}
					} else if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("J");
						} else {
							computer_text_add_character("j");
						}
					}
				} else if (key_freshly_pressed["BracketLeft"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Z");
						} else {
							computer_text_add_character("z");
						}
					}
				} else if (key_freshly_pressed["BracketRight"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("W");
						} else {
							computer_text_add_character("w");
						}
					}
				} else if (key_freshly_pressed["Backslash"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Ç");
						} else {
							computer_text_add_character("ç");
						}
					}
				// Middle range
				} else if (key_freshly_pressed["KeyA"]) {
					if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Â");
							} else {
								computer_text_add_character("â");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("A");
							} else {
								computer_text_add_character("a");
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("l");
						computer_text_add_character("i");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyS"]) {
					if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Û");
							} else {
								computer_text_add_character("û");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["IntlBackslash"]) {
								if (key_pressed["AltRight"]) {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("¦");
									} else {
										computer_text_add_character("↑");
									}
								} else {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("Ư");
									} else {
										computer_text_add_character("ư");
									}
								}
							} else {
								if (key_pressed["AltRight"]) {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("Ù");
									} else {
										computer_text_add_character("ù");
									}
								} else {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("U");
									} else {
										computer_text_add_character("u");
									}
								}
							}

						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("e");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyD"]) {
					if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Î");
							} else {
								computer_text_add_character("î");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("I");
							} else {
								computer_text_add_character("i");
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("t");
						computer_text_add_character("a");
						computer_text_add_character("w");
						computer_text_add_character("a");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyF"]) {
					if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Ê");
							} else {
								computer_text_add_character("ê");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("E");
							} else {
								computer_text_add_character("e");
							}
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("l");
						computer_text_add_character("o");
						computer_text_add_character("n");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["KeyG"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character(";");
						} else {
							computer_text_add_character(",");
						}
					}
				} else if (key_freshly_pressed["KeyH"]) {
					if (key_pressed["ControlLeft"] || key_pressed["ControlRight"]) {
						if (key_pressed["CapsLock"]) {
							computer_text_tech_keyboard_number = 0; // Web
							computer_text_tech_keyboard = true;
						}
					} else if (computer_text_keyboard == 0) {
						if (computer_text_circumflex == true) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("Ĉ");
							} else {
								computer_text_add_character("ĉ");
							}
							
							computer_text_circumflex = false;
						} else {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("C");
							} else {
								computer_text_add_character("c");
							}
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("В");
						} else {
							computer_text_add_character("в");
						}
					}
				} else if (key_freshly_pressed["KeyJ"]) {
					if (computer_text_tech_keyboard && !tad_1001_dialog_shown) {
						if (computer_text_tech_keyboard_number == 0) {
							tad_1001_dialog_shown = true;
							tad_1001_dialog_title = "Title";
						}
					} else {
						if (computer_text_keyboard == 0) {
							if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
								computer_text_add_character("T");
							} else {
								computer_text_add_character("t");
							}
						} else if (computer_text_keyboard == 2) {
							computer_text_add_character("m");
							computer_text_add_character("i");
							computer_text_add_character(" ");
						} else if (computer_text_keyboard == 1) {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("Ң");
								} else {
									computer_text_add_character("ң");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("Н");//қҚ
								} else {
									computer_text_add_character("н");
								}
							}
						}
					}
				} else if (key_freshly_pressed["KeyK"]) {
					if (key_pressed["ControlLeft"] || key_pressed["ControlRight"]) {
						if (key_pressed["CapsLock"]) {
							computer_text_keyboard = 1; // Cyrillic
							computer_text_tech_keyboard = false;
						}
					} else {
						if (computer_text_tech_keyboard && !tad_1001_dialog_shown) {
							if (computer_text_tech_keyboard_number == 0) {
								computer_text_add_character("<");
								computer_text_add_character("a");
								computer_text_add_character(" ");
								computer_text_add_character("h");
								computer_text_add_character("r");
								computer_text_add_character("e");
								computer_text_add_character("f");
								computer_text_add_character("=");
								computer_text_add_character("\"");
								
								if (typeof navigator.clipboard.readtext === "function") {
								navigator.clipboard.readText().then(
									clipText => temp_text = clipText);
								}
								
								if (temp_text[0] == "h" && temp_text[1] == "t" && temp_text[2] == "t" && temp_text[3] == "p") {
									
									
									for (i_BLABLA = 0; i_BLABLA < temp_text.length; i_BLABLA++) {
										computer_text_add_character(temp_text[i_BLABLA]);
									}
								}
								
								computer_text_add_character("\"");
								computer_text_add_character(">");
								computer_text_add_character("<");
								computer_text_add_character("/");
								computer_text_add_character("a");
								computer_text_add_character(">");
								
								computer_text_cursor_position_column -= 4;
							}
						} else {
							if (computer_text_keyboard == 0) {
								if (computer_text_circumflex == true) {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("Ŝ");
									} else {
										computer_text_add_character("ŝ");
									}
									
									computer_text_circumflex = false;
								} else {
									if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
										computer_text_add_character("S");
									} else {
										computer_text_add_character("s");
									}
								}
							} else if (computer_text_keyboard == 2) {
								computer_text_add_character("s");
								computer_text_add_character("i");
								computer_text_add_character("n");
								computer_text_add_character("a");
								computer_text_add_character(" ");
							} else if (computer_text_keyboard == 1) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("Т");
								} else {
									computer_text_add_character("т");
								}
							}
						}
					}
				} else if (key_freshly_pressed["KeyL"]) {
					if (key_pressed["ControlLeft"] || key_pressed["ControlRight"]) {
						if (key_pressed["CapsLock"]) {
							computer_text_keyboard = 0; // bépo
							computer_text_tech_keyboard = false;
						}
					} else if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("R");
						} else {
							computer_text_add_character("r");
						}
					} else if (computer_text_keyboard == 2) {
						if (key_pressed["IntlBackslash"]) {
							computer_text_add_character("S");
							computer_text_add_character("o");
							computer_text_add_character("n");
							computer_text_add_character("j");
							computer_text_add_character("a");
							computer_text_add_character(" ");
						} else {
							computer_text_add_character("o");
							computer_text_add_character("n");
							computer_text_add_character("a");
							computer_text_add_character(" ");
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("С");
						} else {
							computer_text_add_character("с");
						}
					}
				} else if (key_freshly_pressed["Semicolon"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("N");
						} else {
							computer_text_add_character("n");
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("j");
						computer_text_add_character("a");
						computer_text_add_character("n");
						computer_text_add_character(" ");
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Л");
						} else {
							computer_text_add_character("л");
						}
					}
				} else if (key_freshly_pressed["Quote"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("M");
						} else {
							computer_text_add_character("m");
						}
					} else if (computer_text_keyboard == 1) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Р");
						} else {
							computer_text_add_character("р");
						}
					}
				// Low range
				} else if (key_freshly_pressed["KeyZ"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("À");
						} else {
							computer_text_add_character("à");
						}
					}
				} else if (key_freshly_pressed["KeyX"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Y");
						} else {
							computer_text_add_character("y");
						}
					}
				} else if (key_freshly_pressed["KeyC"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("X");
						} else {
							computer_text_add_character("x");
						}
					}
				} else if (key_freshly_pressed["KeyV"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character(":");
						} else {
							computer_text_add_character(".");
						}
					}
				} else if (key_freshly_pressed["KeyB"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("K");
						} else {
							computer_text_add_character("k");
						}
					}
				} else if (key_freshly_pressed["KeyB"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("K");
						} else {
							computer_text_add_character("k");
						}
					}
				} else if (key_freshly_pressed["KeyN"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("?");
						} else {
							computer_text_add_character("'");
						}
					}
				} else if (key_freshly_pressed["KeyM"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("Q");
						} else {
							computer_text_add_character("q");
						}
					} else if (computer_text_keyboard == 2) {
						computer_text_add_character("p");
						computer_text_add_character("o");
						computer_text_add_character("n");
						computer_text_add_character("a");
						computer_text_add_character(" ");
					}
				} else if (key_freshly_pressed["Comma"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("G");
						} else {
							computer_text_add_character("g");
						}
					}
				} else if (key_freshly_pressed["Period"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("H");
						} else {
							computer_text_add_character("h");
						}
					}
				} else if (key_freshly_pressed["Slash"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("F");
						} else {
							computer_text_add_character("f");
						}
					}
				// Number range
				} else if (key_freshly_pressed["Backquote"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("#");
						} else {
							computer_text_add_character("$");
						}
					}
				} else if (key_freshly_pressed["Digit1"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("1");
						} else {
							computer_text_add_character("\"");
						}
					}
				} else if (key_freshly_pressed["Digit2"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("2");
						} else {
							computer_text_add_character("«");
						}
					}
				} else if (key_freshly_pressed["Digit3"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["IntlBackslash"]) {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("🤩");
								} else {
									computer_text_add_character("😘");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("😍");
								} else {
									computer_text_add_character("❤️");
								}
							}
						} else {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("”");
								} else {
									computer_text_add_character("»");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("3");
								} else {
									computer_text_add_character(">");
								}
							}
						}
					

					}
				} else if (key_freshly_pressed["Digit4"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["IntlBackslash"]) {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("😁");
								} else {
									computer_text_add_character("😀");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("😂");
								} else {
									computer_text_add_character("🙂");
								}
							}
						} else {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("≥");
								} else {
									computer_text_add_character("]");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("4");
								} else {
									computer_text_add_character("(");
								}
							}
						}

					}
				} else if (key_freshly_pressed["Digit5"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["IntlBackslash"]) {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("😁");
								} else {
									computer_text_add_character("😀");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("😂");
								} else {
									computer_text_add_character("🙂");
								}
							}
						} else {
							if (key_pressed["AltRight"]) {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("≥");
								} else {
									computer_text_add_character("]");
								}
							} else {
								if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
									computer_text_add_character("5");
								} else {
									computer_text_add_character(")");
								}
							}
						}

					}
				} else if (key_freshly_pressed["Digit6"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("6");
						} else {
							computer_text_add_character("@");
						}
					}
				} else if (key_freshly_pressed["Digit7"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("7");
						} else {
							computer_text_add_character("+");
						}
					}
				} else if (key_freshly_pressed["Digit8"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("8");
						} else {
							computer_text_add_character("-");
						}
					}
				} else if (key_freshly_pressed["Digit9"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("9");
						} else {
							computer_text_add_character("/");
						}
					}
				} else if (key_freshly_pressed["Digit0"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("0");
						} else {
							computer_text_add_character("*");
						}
					}
				} else if (key_freshly_pressed["Minus"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("°");
						} else {
							computer_text_add_character("=");
						}
					}
				} else if (key_freshly_pressed["Minus"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("°");
						} else {
							computer_text_add_character("=");
						}
					}
				} else if (key_freshly_pressed["Equal"]) {
					if (computer_text_keyboard == 0) {
						if (key_pressed["ShiftLeft"] || key_pressed["ShiftRight"]) {
							computer_text_add_character("`");
						} else {
							computer_text_add_character("%");
						}
					}
				// Arrows
				} else if (key_freshly_pressed["ArrowLeft"]) {
					if (computer_text_cursor_position_column > 1) {
						computer_text_cursor_position_column--;
					} else {
						computer_text_cursor_position_line--;
						computer_text_cursor_position_column = computer_text_showing[computer_text_cursor_position_line].length;
					}
				} else if (key_freshly_pressed["ArrowRight"]) {
					if (computer_text_cursor_position_column < computer_text_showing[computer_text_cursor_position_line].length) {
						computer_text_cursor_position_column++;
					} else {
						computer_text_cursor_position_column = 1;
						computer_text_cursor_position_line++;
					}
				} else if (key_freshly_pressed["ArrowUp"]) {
					computer_text_cursor_position_line--;
				} else if (key_freshly_pressed["ArrowDown"]) {
					computer_text_cursor_position_line++;
				// Delete
				} else if (key_freshly_pressed["Backspace"]) {
					if (computer_text_cursor_position_column > 1) {
						for (i = computer_text_cursor_position_column; i <= computer_text_showing[computer_text_cursor_position_line].length; i++) {
							computer_text_showing[computer_text_cursor_position_line][i - 1] = computer_text_showing[computer_text_cursor_position_line][i];
						}
						
						computer_text_showing[computer_text_cursor_position_line].pop();
						
						computer_text_cursor_position_column--;
					} else if (computer_text_cursor_position_line > 1) {
						computer_text_cursor_position_line--;
						computer_text_cursor_position_column = computer_text_showing[computer_text_cursor_position_line].length;
						
						temp = computer_text_showing[computer_text_cursor_position_line].length;
						
						for (i = 1; i <= computer_text_showing[computer_text_cursor_position_line + 1].length - 1; i++) {
							computer_text_showing[computer_text_cursor_position_line][temp + i - 1] = computer_text_showing[computer_text_cursor_position_line + 1][i];
						}
						
						for (i = computer_text_cursor_position_line + 2; i < 99999; i++) {
							computer_text_showing[i - 1] = computer_text_showing[i];
						}
					}
				// Enter
				} else if (key_freshly_pressed["Enter"]) {
					if (tad_1001_dialog_shown) {
						if (tad_1001_dialog_title = "Title" && tad_1001_dialog_content.length > 0) {
							console.log("Hello");
							
							tad_1001_dialog_shown = false;
							
							computer_text_add_character("<");
							computer_text_add_character("!");
							computer_text_add_character("D");
							computer_text_add_character("O");
							computer_text_add_character("C");
							computer_text_add_character("T");
							computer_text_add_character("Y");
							computer_text_add_character("P");
							computer_text_add_character("E");
							computer_text_add_character(" ");
							computer_text_add_character("h");
							computer_text_add_character("t");
							computer_text_add_character("m");
							computer_text_add_character("l");
							computer_text_add_character(">");
							computer_text_add_break();
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("m");
							computer_text_add_character("e");
							computer_text_add_character("t");
							computer_text_add_character("a");
							computer_text_add_character(" ");
							computer_text_add_character("c");
							computer_text_add_character("h");
							computer_text_add_character("a");
							computer_text_add_character("r");
							computer_text_add_character("s");
							computer_text_add_character("e");
							computer_text_add_character("t");
							computer_text_add_character("=");
							computer_text_add_character("\"");
							computer_text_add_character("u");
							computer_text_add_character("t");
							computer_text_add_character("f");
							computer_text_add_character("-");
							computer_text_add_character("8");
							computer_text_add_character("\"");
							computer_text_add_character(" ");
							computer_text_add_character("/");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("m");
							computer_text_add_character("e");
							computer_text_add_character("t");
							computer_text_add_character("a");
							computer_text_add_character(" ");
							computer_text_add_character("n");
							computer_text_add_character("a");
							computer_text_add_character("m");
							computer_text_add_character("e");
							computer_text_add_character("=");
							computer_text_add_character("\"");
							computer_text_add_character("v");
							computer_text_add_character("i");
							computer_text_add_character("e");
							computer_text_add_character("w");
							computer_text_add_character("p");
							computer_text_add_character("o");
							computer_text_add_character("r");
							computer_text_add_character("t");
							computer_text_add_character("\"");
							computer_text_add_character(" ");
							computer_text_add_character("c");
							computer_text_add_character("o");
							computer_text_add_character("n");
							computer_text_add_character("t");
							computer_text_add_character("e");
							computer_text_add_character("n");
							computer_text_add_character("t");
							computer_text_add_character("=");
							computer_text_add_character("\"");
							computer_text_add_character("w");
							computer_text_add_character("i");
							computer_text_add_character("d");
							computer_text_add_character("t");
							computer_text_add_character("h");
							computer_text_add_character("=");
							computer_text_add_character("d");
							computer_text_add_character("e");
							computer_text_add_character("v");
							computer_text_add_character("i");
							computer_text_add_character("c");
							computer_text_add_character("e");
							computer_text_add_character("-");
							computer_text_add_character("w");
							computer_text_add_character("i");
							computer_text_add_character("d");
							computer_text_add_character("t");
							computer_text_add_character("h");
							computer_text_add_character(",");
							computer_text_add_character(" ");
							computer_text_add_character("i");
							computer_text_add_character("n");
							computer_text_add_character("i");
							computer_text_add_character("t");
							computer_text_add_character("i");
							computer_text_add_character("a");
							computer_text_add_character("l");
							computer_text_add_character("-");
							computer_text_add_character("s");
							computer_text_add_character("c");
							computer_text_add_character("a");
							computer_text_add_character("l");
							computer_text_add_character("e");
							computer_text_add_character("=");
							computer_text_add_character("1");
							computer_text_add_character("\"");
							computer_text_add_character("");
							computer_text_add_character("/");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("h");
							computer_text_add_character("t");
							computer_text_add_character("m");
							computer_text_add_character("l");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("h");
							computer_text_add_character("e");
							computer_text_add_character("a");
							computer_text_add_character("d");
							computer_text_add_character(">");
							computer_text_add_break();
							
							
							
							computer_text_add_character("<");
							computer_text_add_character("t");
							computer_text_add_character("i");
							computer_text_add_character("t");
							computer_text_add_character("l");
							computer_text_add_character("e");
							computer_text_add_character(">");
							
							for (i_BLABLA = 0; i_BLABLA < tad_1001_dialog_content.length; i_BLABLA++) {
								computer_text_add_character(tad_1001_dialog_content[i_BLABLA]);
							}
							
							computer_text_add_character("<");
							computer_text_add_character("/");
							computer_text_add_character("t");
							computer_text_add_character("i");
							computer_text_add_character("t");
							computer_text_add_character("l");
							computer_text_add_character("e");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("/");
							computer_text_add_character("h");
							computer_text_add_character("e");
							computer_text_add_character("a");
							computer_text_add_character("d");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("b");
							computer_text_add_character("o");
							computer_text_add_character("d");
							computer_text_add_character("y");
							computer_text_add_character(">");
							computer_text_add_break();
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("/");
							computer_text_add_character("b");
							computer_text_add_character("o");
							computer_text_add_character("d");
							computer_text_add_character("y");
							computer_text_add_character(">");
							computer_text_add_break();
							
							computer_text_add_character("<");
							computer_text_add_character("/");
							computer_text_add_character("h");
							computer_text_add_character("t");
							computer_text_add_character("m");
							computer_text_add_character("l");
							computer_text_add_character(">");
							
							computer_text_cursor_position_column = 1;
							computer_text_cursor_position_line -= 2;
						}
					} else {computer_text_add_break();}
					
				// Space
				} else if (key_freshly_pressed["Space"]) {
					if (computer_text_keyboard == 2) {
						computer_text_add_character("t");
						computer_text_add_character("o");
						computer_text_add_character("k");
						computer_text_add_character("i");
						computer_text_add_character(" ");
					} else {
						computer_text_add_character(" ");
					}
				}
				
				context.font = "" + canvas.height/55 + "px monospace";
				context.fillStyle = "white";
				
				for (i = 1; i < 30; i++) {
					if (i == computer_text_cursor_position_line) {
						context.fillStyle = "yellow";
					} else {
						context.fillStyle = "red";
					}
					
					context.fillText(i, 0, canvas.height/25 + i*canvas.width/51 - canvas.height/200);
					

					
					context.fillStyle = "white";
					
					for (j = 1; j < computer_text_showing[i].length; j++) {
						context.fillText(computer_text_showing[i][j], (2+j)*canvas.width/51, canvas.height/25 + i*canvas.width/51 - canvas.height/200);
					}
				}
				
				for (i = 1; i < 50; i++) {
					if (i == computer_text_cursor_position_column) {
						context.fillStyle = "yellow";
					} else {
						context.fillStyle = "red";
					}
					
					i_text = "" + i;
					context.fillText(i_text[0], (2+i)*canvas.width/51, canvas.height/50 - canvas.height/200);
					
					if (i > 9) {
						context.fillText(i_text[1], (2+i)*canvas.width/51, canvas.height/25 - canvas.height/200);
					}
				}
				
				if (tad_1001_dialog_shown) {
					context.fillStyle = "rgb(0,0,255)";
					
					context.fillRect(canvas.width / 7, 3 * canvas.height / 4, 5 * canvas.width / 7, canvas.height/15);
					
					
					
					context.fillStyle = "rgb(255,255,255)";
					
					context.fillRect(canvas.width / 7 + canvas.width/200, 3 * canvas.height / 4 + canvas.height/30 - canvas.height/200, 5 * canvas.width / 7 - canvas.width / 100, canvas.height/30);
					
					context.fillStyle = "rgb(0,0,0)";
					
					context.font = "" + canvas.height/33 + "px monospace";
					
					context.fillText(tad_1001_dialog_title, canvas.width / 7 + canvas.width/200, 3 * canvas.height / 4 + canvas.height/30 - canvas.height / 100);
					
					if (tad_1001_dialog_content.length > 0) {
						for (i = 0; i < tad_1001_dialog_content.length; i++) {
							context.fillText(tad_1001_dialog_content[i], canvas.width / 7 + canvas.width/200 + i*canvas.height/30, 3 * canvas.height / 4 + canvas.height/15 - canvas.height / 100);
						}
					}
				} else {
				
					if (miliseconds % 2000 < 1000) {
						context.fillStyle = "rgb(" + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ")";
						context.fillRect((2+computer_text_cursor_position_column)*canvas.width/51 - canvas.width/900, 2+computer_text_cursor_position_line*canvas.width/51, canvas.width/450, canvas.width/51);
					}
				
				}
				
				for (i = 0; i < 10; i++) {
					for (j = 0; j < 12; j++) {
						context.fillStyle = "rgba(" + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ", " + getRandomInt(0,255) + ", 0.2)";
						
						context.font = "" + canvas.height/9 + "px monospace";
						
						context.fillText(computer_bigtext[i][j], (3+4*j)*canvas.width/51, canvas.height/25 + 4*(i + 1)*canvas.width/51 - canvas.height/200);
					}
				}
				
				
				
				context.fillText(computer_text, canvas.width/200, canvas.height/2);
				
				temp_text = "computer text:";
				
				for (i = 1; i < computer_text_showing[1].length; i++) {
					temp_text += computer_text_showing[1][i];
				}
				
				ajax_input[ajax_input.length] = temp_text;
			} else if (learned_language == 15) { //3d test
				
				
				x_factor = 1;
				
				y_factor = 1;
				
				x_positivity = 0;
				y_positivity = 0;
				
				if (key_pressed["KeyQ"]) {
					x_factor = 2;
				} else if (key_pressed["KeyW"]) {
					x_factor = 4;
				}
				
				if (key_pressed["KeyA"]) {
					y_factor = 2;
				} else if (key_pressed["KeyS"]) {
					y_factor = 4;
				}
				
				if (key_pressed["ArrowLeft"]) {
					x_positivity = -1;
				}
				
				if (key_pressed["ArrowRight"]) {
					x_positivity = 1;
				}
				
				if (key_pressed["ArrowUp"]) {
					y_positivity = -1;
				}
				
				if (key_pressed["ArrowDown"]) {
					y_positivity = 1;
				}
				
				anglex = performance.now() / 1000 / 6 * 2 * Math.PI;
				angley = performance.now() / 1000 / 6 * 2 * Math.PI;
				
				
				/*
				if (x_positivity > -1) {for (i = 0; i < x_factor; i++) {
					mat4.rotate(yRotationMatrix, identityMatrix, angle * (0.1), [0, 1, 0]);
				}}
				
				if (x_positivity < 1) {for (i = 0; i < x_factor; i++) {
					mat4.rotate(yRotationMatrix, identityMatrix, angle * (-0.1), [0, 1, 0]);
				}}
				
				if (y_positivity > -1) {for (i = 0; i < y_factor; i++) {
					mat4.rotate(xRotationMatrix, identityMatrix, angle * (0.1), [1, 0, 0]);
				}}
				
				if (y_positivity < 1) {for (i = 0; i < y_factor; i++) {
					mat4.rotate(xRotationMatrix, identityMatrix, angle * (-0.1), [1, 0, 0]);
				}}*/
				
				if (y_positivity != 0 || test_3d_freshly_chosen) {
					mat4.rotate(yRotationMatrix, identityMatrix, angley * (0.1 * y_factor * y_positivity), [0, 1, 0]);
				} else {
					mat4.rotate(yRotationMatrix, identityMatrix, angley * (0.1 * y_factor * y_positivity), [0, 0, 0]);
				}
				
				if (x_positivity != 0 || test_3d_freshly_chosen) {
					mat4.rotate(xRotationMatrix, identityMatrix, anglex * (0.1 * x_factor * x_positivity), [1, 0, 0]);
				} else {
					mat4.rotate(xRotationMatrix, identityMatrix, anglex * (0.1 * x_factor * x_positivity), [0, 0, 0]);
				}
				
				
				mat4.mul(worldMatrix, yRotationMatrix, xRotationMatrix);
				gl.uniformMatrix4fv(matWorldUniformLocation, gl.FALSE, worldMatrix);

				gl.clearColor(1.0, 0.0, 1.0, 1.0);
				gl.clear(gl.DEPTH_BUFFER_BIT | gl.COLOR_BUFFER_BIT);

				gl.bindTexture(gl.TEXTURE_2D, boxTexture);
				gl.activeTexture(gl.TEXTURE0);

				gl.drawElements(gl.TRIANGLES, boxIndices.length, gl.UNSIGNED_SHORT, 0);

				//requestAnimationFrame(loop);
				
				test_3d_freshly_chosen = false;
			}
		}
	}
	
	//
	//
	// Post-activities
	//
	//
	
	//
	// Draw the ActiveLines
	//
	
	// ActiveLine
	if (!three_dimensions_shown) {
	context.beginPath();
	context.moveTo(getRandomInt(1, canvas.width - 1), getRandomInt(1, 17*canvas.height/18 - 1));
	context.lineTo(getRandomInt(1, canvas.width - 1), getRandomInt(1, 17*canvas.height/18 - 1));
	context.lineWidth = canvas.height / 400;
	context.strokeStyle = "rgb(" + getRandomInt(0,255) + "," + getRandomInt(0,255) + "," + getRandomInt(0,255) + ")";
	context.stroke();
	}
	
	/*
	
	// ActiveLine 10
	if (miliseconds % 100 == 0) {
		activeline_10_start_x = getRandomInt(1, canvas.width - 1);
		activeline_10_start_y = getRandomInt(1, 17*canvas.height/18 - 1);
		activeline_10_end_x = getRandomInt(1, canvas.width - 1);
		activeline_10_end_y = getRandomInt(1, 17*canvas.height/18 - 1);
		activeline_10_red = getRandomInt(0,255);
		activeline_10_green = getRandomInt(0,255);
		activeline_10_blue = getRandomInt(0,255);
	}

	context.beginPath();
	context.moveTo(activeline_10_start_x, activeline_10_start_y);
	context.lineTo(activeline_10_end_x, activeline_10_end_y);
	context.lineWidth = canvas.height / 300;
	context.strokeStyle = "rgb(" + activeline_10_red + "," + activeline_10_green + "," + activeline_10_blue + ")";
	context.stroke();
	
	// ActiveLine 16
	if (miliseconds % 400 == 0) {
		activeline_16_start_x = getRandomInt(1, canvas.width - 1);
		activeline_16_start_y = getRandomInt(1, 17*canvas.height/18 - 1);
		activeline_16_end_x = getRandomInt(1, canvas.width - 1);
		activeline_16_end_y = getRandomInt(1, 17*canvas.height/18 - 1);
		activeline_16_red = getRandomInt(0,255);
		activeline_16_green = getRandomInt(0,255);
		activeline_16_blue = getRandomInt(0,255);
	}

	context.beginPath();
	context.moveTo(activeline_16_start_x, activeline_16_start_y);
	context.lineTo(activeline_16_end_x, activeline_16_end_y);
	context.lineWidth = canvas.height / 300;
	context.strokeStyle = "rgb(" + activeline_16_red + "," + activeline_16_green + "," + activeline_16_blue + ")";
	context.stroke();*/
	
	//
	// Draw the DogLine
	//
	context.fillStyle = "black";
	if (three_dimensions_shown) {
		context.fillRect(0,0,canvas.width,canvas.height);
	} else {
		context.fillRect(0,canvas.height/18*17,canvas.width,canvas.height/18);
	}
	
	// Get the KeyReport
	keyreport = "";
	/*
	for (i = 0; i < key_pressed.length; i++) {
	
		if (key_pressed[i] == true) {
			keyreport += "(" + i + ") " + key_name_for_keyreport[i] + " ";
		}
	}*/
	/*
	key_pressed.forEach(function(element,index) {
		console.log("Hello! " + index + " is " + element);
	
		if (key_pressed[index] == true) {
			keyreport += key_pressed[index] + " ";
		}
	});*/
	
	for (var key in key_pressed) {
		if (key_pressed[key] == true) {
			keyreport += key + " ";
			
			if (key == "IntlBackslash") {
				keyreport += "\"Intensity\" ";
			}
		}
	}
	
	if (keyreport == "") {
		context.fillStyle = "white";
	} else {
		context.fillStyle = "rgb(128,128,128)";
	}
	
	if (three_dimensions_shown) {
		context.font = "" + canvas.height/2 + "px monospace";
	} else {
		context.font = "" + canvas.height/36 + "px monospace";
	}
	
	i_message = dogline_oldest_message;
	i_letter = dogline_oldest_letter;
	i_sinus = miliseconds / 200;
	
	for (i = 0; i<=49; i++) {

		i_sinus += 0.3;
		
		if (i_message != -1) { 
			if (i_letter < dogline_message[i_message].length) {
				if (three_dimensions_shown) {
					context.fillText(dogline_message[i_message][i_letter], (i-dogline_oldest_subposition/100)*canvas.width/48, canvas.height/29 * 18 + Math.sin(i_sinus) * canvas.height/100 * 18);
				} else {
					context.fillText(dogline_message[i_message][i_letter], (i-dogline_oldest_subposition/100)*canvas.width/48, 17*canvas.height/18 + canvas.height/29 + Math.sin(i_sinus) * canvas.height/100);
				}
			} else if (i_letter == dogline_message[i_message].length + 1) {
				if (three_dimensions_shown) {
					context.fillRect((i-dogline_oldest_subposition/100)*canvas.width/48 - canvas.height/2, 0, canvas.height, canvas.height);
				} else {
					context.fillRect((i-dogline_oldest_subposition/100)*canvas.width/48 - canvas.height/36, 17*canvas.height/18, canvas.height/18, canvas.height/18);
				}
			}
			
			i_letter++;
			if (i_letter > dogline_message[i_message].length + 2) {
				i_letter = 0;
				if (i_message < dogline_message.length - 1) {
					i_message++;
				} else {
					i_message = -1;
				}
			}
		}
		

	}
	
	// Activate speed
	if (i_message != -1) {
		if (!backspeed) {
			while (i_message < dogline_message.length) {
				dogline_oldest_subposition += dogline_speed[i_message];
				
				if (dogline_oldest_subposition > 99) {
					dogline_oldest_letter += Math.floor(dogline_oldest_subposition / 100);
					
					if (dogline_oldest_letter > dogline_message[dogline_oldest_message].length + 2) {
						dogline_oldest_letter -= dogline_message[dogline_oldest_message].length + 3;
						dogline_oldest_message++;
					}
					
					dogline_oldest_subposition = dogline_oldest_subposition % 100;
				}
				
				i_message++;
			}
		}
	} else {
		backspeed = true;
	}
	
	// Backspeed
	if (backspeed && dogline_oldest_message != 0) {
		dogline_oldest_subposition -= 5;
		
		if (dogline_oldest_subposition < 0) {
			dogline_oldest_letter--;
			
			if (dogline_oldest_letter < 0) {
				dogline_oldest_message--;
				dogline_oldest_letter = dogline_message[dogline_oldest_message].length + 2;
				
				if (dogline_oldest_message == 0) {
					backspeed = false;
				}
			}
		
			dogline_oldest_subposition += 100;
		}
	}
	
	// KeyReport
	context.fillStyle = "white";
	if (three_dimensions_shown) {
		context.font = "" + canvas.height/44 * 18 + "px monospace";
	} else {
		context.font = "" + canvas.height/44 + "px monospace";
	}
	context.fillText(keyreport, 0, canvas.height * 0.99);
	
	// Fill the page not covered by the canvas
	body.style.backgroundColor = "rgb(" + getRandomInt(170,255) + "," + getRandomInt(170,255) + "," + getRandomInt(170,255) + ")";

	// Time lost messages
	if (miliseconds == 10000) {
		dogline_message[dogline_message.length] = ("" + dogline_10_s_lost[learned_languages[learned_language]] + " / " + dogline_10_s_lost[learning_languages[learning_language]]).split("");
		dogline_speed[dogline_speed.length] = 25;
		backspeed = false;
	}

	if (miliseconds == 60000) {
		dogline_message[dogline_message.length] = ("" + dogline_1_min_lost[learned_languages[learned_language]] + " / " + dogline_1_min_lost[learning_languages[learning_language]]).split("");
		dogline_speed[dogline_speed.length] = 25;
		backspeed = false;
	}

	if (miliseconds == 600000) {
		dogline_message[dogline_message.length] = ("" + dogline_10_min_lost[learned_languages[learned_language]] + " / " + dogline_10_min_lost[learning_languages[learning_language]]).split("");
		dogline_speed[dogline_speed.length] = 25;
		backspeed = false;
	}

	if (miliseconds == 1800000) {
		dogline_message[dogline_message.length] = ("" + dogline_30_min_lost[learned_languages[learned_language]] + " / " + dogline_30_min_lost[learning_languages[learning_language]]).split("");
		dogline_speed[dogline_speed.length] = 25;
		backspeed = false;
	}

	if (miliseconds == 3600000) {
		dogline_message[dogline_message.length] = ("" + dogline_1_h_lost[learned_languages[learned_language]] + " / " + dogline_1_h_lost[learning_languages[learning_language]]).split("");
		dogline_speed[dogline_speed.length] = 25;
		backspeed = false;
	}
	
	for(var key in key_freshly_pressed) {
		key_freshly_pressed[key] = false;
		key_freshly_released[key] = false;
	}
	
	clicking = false;
	
	if (main_menu || learned_language != 10) {
		ajax_input[ajax_input.length] = "Number:" + getRandomInt(0,1000000000);
	}
	
	// AjaxReport
	context.fillText(ajax_time, canvas.width * 0.9, canvas.height * 0.99);
	
	miliseconds += 25;
}
/*
function ajax() {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		ajax_output = this.responseText;
		console.log(ajax_output);
		ajax();
	}
	};
	xhttp.open("POST", "https://ins-phina.com/suhin-2-0-6_ajax.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	temp_text = "nothing=nothing";
	
	if (ajax_input.length > 0) {
		for (i = 0; i < ajax_input.length; i++) {
			temp_text += "&input[" + i + "]=" + encodeURIComponent(ajax_input[i]);
		}
	}
	
	xhttp.send(temp_text);
	ajax_time = ajax_input.length;
	ajax_input.length = 0;
}*/

setInterval(function() {step();}, 25);
//ajax();

</script>
</body>
</html>
<?php
} else {
	header("Location: https://www.suhin.org/");
}
