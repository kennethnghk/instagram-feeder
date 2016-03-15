var APP = {
	mode 	: 'createdtime', 			// the sorting mode
	offset	: '',						// offset for getting feed
	feedUrl	: '/public/api/feed/media', // feed URL
	userFavMediaUrl : 'public/api/user/favmedia', // user fav media API URL
	loading	: 0,						// prevent multiple ajax calls at same time
	showPlayBtn : 1,					// whether it shows video play button
	imageRes : 'standard',				// image resolution
	pagesize : 10,						// no. of media shown in each scroll
	favids   : [],						// user fav ids
	newSession : 1						// whether it is new session
};

APP.linkifyText = function (text) {
	text = text.replace(/#([^\s#]+)/g, '<a href="https://instagram.com/explore/tags/$1/" target="_blank">#$1</a>');
	text = text.replace(/@([^\s#]+)/g, '<a href="https://instagram.com/$1/" target="_blank">@$1</a>');
	return text;
};

// get user fav ids from server / after add, update into local session storage
APP.updateUserFav = function (data) {
	
	// update direct when add / remove action
	if (typeof(data) != 'undefined') {
		sessionStorage.favids = data;
      	APP.favids = data;
      	$(APP).trigger('favUpdated');
      	return;
	}
	
	// update from server
	var api = '/public/api/user/favids';

  	$.ajax({
      	url: api,
      	dataType: 'json',
      	method: 'post',
      	data: {username: sessionStorage.username},
      	cache: false,
      	success: function(data) {
      		sessionStorage.favids = data.data;
      		APP.favids = data.data;
      		$(APP).trigger('favUpdated');
      		
      	}.bind(this),
      
      	error: function(xhr, status, err) {
        	console.error(api, status, err.toString());
        	
      	}.bind(this)
    });
    //
}

// image content
var ImageContent = React.createClass({

	render: function() {
		return (
			<img src={this.props.src} className="lazy" />
		);
	}
});
//

// video content
var VideoContent = React.createClass({

	// handle click on video btn
	handleClickBtn: function(e) {
		e.preventDefault();
		
		// stop video which is playing
		if (APP.onPlayVideo) {
			APP.onPlayVideo.pause();
			$('.btn').show();
		}
		
		React.findDOMNode(this.refs.player).play();
		$(React.findDOMNode(this.refs.btn)).hide();
		APP.onPlayVideo = React.findDOMNode(this.refs.player);
		return;
	},

	// handle click on video player
	handleClickVideo: function(e) {
		e.preventDefault();
		React.findDOMNode(this.refs.player).pause();
		$(React.findDOMNode(this.refs.btn)).show();
		return;
	},
		
	render: function() {
	
		var playBtn;
		if (APP.showPlayBtn == 1)
			playBtn = <div onClick={this.handleClickBtn} className="btn" ref="btn"><div className="playIcon" ></div></div>;
	
		return (
			<div className="player">
				<video preload="auto" ref="player" onClick={this.handleClickVideo} poster={this.props.poster}>
 					<source src={this.props.video.standard_resolution.url} type='video/mp4' />
 					<p className="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that supports HTML5 video</p>
				</video>
				{playBtn}
			</div>
		);
	}
});
//

// fav. button 
var FavBtn = React.createClass({

	getInitialState: function() {
	
		var id = this.props.id;
		 
		// define whether it is fav.
		var isFav = 0;
		if ($.inArray(id, APP.favids) != -1)
			isFav = 1;
			
    	return {id 		: id, 
    			isFav 	: isFav};
  	},

	// handle click event on fav. button
	handleClick: function() {
	
		var btn = React.findDOMNode(this.refs.btn);
		$(btn).toggleClass('clicked');
		
		// update to server
		var action = 'addfav';
		if (this.state.isFav == 1)
			action = 'removefav';
				
		$.ajax({
      		url: '/public/api/user/'+action,
      		dataType: 'json',
      		method: 'post',
      		data: {username: sessionStorage.username, id :this.state.id },
      		cache: false,
      		success: function(data) {
      			
      			APP.updateUserFav(data.data);
      				
      		}.bind(this),
      
      		error: function(xhr, status, err) {
        		console.error(api, status, err.toString());	
      		}.bind(this)
    	});
    	//

	},

  	componentDidMount: function() {
    
    	// triggered when user fav list is updated, then update the display of fav. button
    	$(APP).on('favUpdated', function(e){
    	
    		var isFav = 0;
			if ($.inArray(this.props.id, APP.favids) != -1)
				isFav = 1;
    	
      		this.setState({isFav:isFav});
    	}.bind(this));
    	//
  	},

	render: function() {
		
		var favBtn = <div className="favBtn" onClick={this.handleClick} ref="btn"/>;
		if (this.state.isFav == 1) 
			favBtn = <div className="favBtn clicked" onClick={this.handleClick} ref="btn"/>
	
		return (
			<div>{favBtn}</div>
		);
	}
});
//

// individual media 
var Media = React.createClass({

	getInitialState: function() {
	
		var showFavBtn = 0;
		if (APP.newSession == 0)	
			showFavBtn = 1;
			
    	return {showFavBtn : showFavBtn};
  	},

  	componentDidMount: function() {
    
    	// when user is added, show favourite button
    	$(APP).on('userAdded', function(e){
      		this.setState({showFavBtn:1});
    	}.bind(this));
    	//
  	},

  	render: function() {	
  		
  		var imageUrl = this.props.image.standard_resolution.url;
  		if (APP.imageRes == 'low') 
  			imageUrl = this.props.image.low_resolution.url;
  		
  		var content;
  		if (this.props.video) {
  			content = <VideoContent video={this.props.video} poster={imageUrl}/>;
  		} else {
  			content = <ImageContent src={imageUrl} />;
  		}
  	
  		var favBtn = '';
  		if (this.state.showFavBtn == 1) {
  			favBtn = <FavBtn id={this.props.id}/>;
  		}
  			
    	return (
    		<div className="box col-xs-12 col-sm-12 col-md-12 col-lg-12">
    			<div className="item clearfix"> 
    				<div className="photo">{content}</div>
        			<div className="caption" dangerouslySetInnerHTML={{__html: APP.linkifyText(this.props.children)}}></div>
        			<div className="info"><a href={this.props.uri} target="_blank">{this.props.created_time_diff}</a>&nbsp;&middot;&nbsp;{this.props.like} likes&nbsp;&nbsp;{this.props.comment} comments</div>
					{favBtn}
        		</div>
      		</div>
    	);
  	}
});
//

// list of several media posts
var MediaList = React.createClass({

  	render: function() {
		var mediaNodes = this.props.data.map(function (data) {
    	return (
        	<Media 	id={data.id}
        			image={data.images}
        			video={data.videos}
        	 		like={data.likes.count} 
        	 		comment={data.comments.count} 
        	 		uri={data.link}
        	 		created_time_diff={data.created_time_diff}>
          		{data.caption ? data.caption.text : ''}
        	</Media>
      	);
    });
    
    return (
    	<div>
    		{mediaNodes}
      		<div className="loadingMsg">loading..</div>
      	</div> 
    );
  }
}); 
//

// the main container
var MediaContainer = React.createClass({

	getInitialState: function() {
  		window.addEventListener("scroll", this.handleScroll);
    	return {data: []};
  	},
  
  	// get media from server, append to list
  	loadFromServer: function() {
  
  		APP.loading = 1;
  		var mediaList = this.state.data;
  	
  		api = APP.feedUrl+'/'+APP.mode;
  		if (APP.mode == 'fav')
  			api = APP.userFavMediaUrl;
  		
  		api += '/'+APP.pagesize;
  		if (APP.offset != '') 
  			api += '/'+APP.offset;
  	
  		var ajaxParams = {	
  							url: api,
      						dataType: 'json',
      						cache: true,
      						success: function(data) {
      			
      							if (data.total > 0) {
      								var newMediaList = mediaList.concat(data.data);      
        							this.setState({data: newMediaList});
        
        							APP.offset = data.next_offset;
        						} else {
        							$('.loadingMsg').html('No more posts');
        						}
								APP.loading = 0;
      						}.bind(this),
      
      						error: function(xhr, status, err) {
        						console.error(api, status, err.toString());
        						APP.loading = 0;
      						}.bind(this)
    	};
    	
    	if (APP.mode == 'fav') {
    		ajaxParams.method = 'post';
    		ajaxParams.data	  = {username : sessionStorage.username};
    	}
  	
    	$.ajax(ajaxParams);
	},
  
  	// handle scroll to bottom
  	handleScroll:function(e){   
   		if($(window).scrollTop() + $(window).height() >= $(document).height() - 800){
   	  		if (APP.loading == 0) {
      			this.loadFromServer();
      		}
    	}
  	},
    
  	componentDidMount: function() {
    	this.loadFromServer();  
    
    	// change in dropdown mode
    	$(APP).on('modeChange', function(e){
      		this.setState({data:[]});
      		this.state.data = [];
      		this.loadFromServer(); 
    	}.bind(this));
    	//
  	},
  	
  	render: function() {
    	return (
        	<MediaList data={this.state.data} />
    	);
  	}
});
//

// Dropdown list class
var DropdownList = React.createClass({

	// handle change of dropdown
  	handleChange : function(e) {
    	e.preventDefault();
    	APP.mode = React.findDOMNode(this).value;
    	APP.offset = '';
    	console.log(APP.newSession);
    	$(APP).trigger('modeChange');
        return;
  	},

  	render: function() {
    	var optionNodes = this.props.options.map(function (option) {
      		return (
        		<option ref="mode" value={option.mode}>{option.label}</option>
      		);
    	});
    	
    	return (
      		<select className="form-control" onChange={this.handleChange}>
        		{optionNodes}
      		</select>
    	);
  	}
});
//

// Dropdown class
var Dropdown = React.createClass({
	render: function() {
    	return (
    		<div className="control col-xs-4 col-sm-3 col-md-3 col-lg-3">
    			<DropdownList options={this.props.options}/>
    		</div>
    	);
    }
});
//

// Title class
var Title = React.createClass({

	render: function() {
		
		var name = this.props.name;
		if (this.props.newSession == 0) 
			name = 'Welcome back, '+sessionStorage.username;
	
    	return (
    		<div className="title col-xs-8 col-sm-9 col-md-9 col-lg-9">{name}</div>
    	);
    }
});
//

// header class
var Header = React.createClass({

	getInitialState: function() {
    	return { titleName: "9GAG", 
    			 dropdownOptions: APP.dropdownOptions};
  	},

	// handle submit in username
	handleSumbit : function (e) {
		
		e.preventDefault();
			
		sessionStorage.username = React.findDOMNode(this.refs.username).value;
        APP.dropdownOptions.push({label: "Favourite", mode: "fav"});
           	
        this.setState({ titleName: 'Hi, '+ sessionStorage.username,
          				dropdownOptions: APP.dropdownOptions});
    	
    	$('.usernameInput').slideUp();
           	
        APP.updateUserFav();
        APP.newSession = 0;
        $(APP).trigger('userAdded');
        

	},
	
	componentDidMount: function() {
        $('.usernameInput .btn').on('click', this.handleSumbit);
	},

	render: function() {
	
		var usernameInput = <div className="usernameInput">
								<div className="col-xs-9 col-sm-9 col-md-9 col-lg-9">
									<input type="text" ref="username" className="username form-control" placeholder="Hello, enter your name here." />
								</div>
								
									<button type="button" className="btn btn-primary">Submit</button>
								
							</div>;
							
		if (APP.newSession == 0)
			usernameInput = '';
	
		
    	return (
        	<div className="header clearfix">
				<div className="content col-xs-12 col-sm-12 col-md-12 col-lg-12">
					
					<Title name={this.state.titleName} newSession={APP.newSession} />
					<Dropdown options={this.state.dropdownOptions}/>
					{usernameInput}
				</div> 
			</div>
    	);
  	}
});
// 

/* Init functions */

// determine show play button
if( /iphone|ipad|ipod/i.test(navigator.userAgent) ) {
	APP.showPlayBtn = 0;
}

// determine image resolution
if ($(window).width() <= 320) APP.imageRes = 'low';

// determine dropdown options
APP.dropdownOptions = [
	{label: "Time",  mode: "createdtime"},
  	{label: "Likes", mode: "like"},
  	{label: "Comments", mode: "comment"},
];

// determine new session 
if (typeof(sessionStorage.username) != 'undefined') {
	APP.newSession = 0;
	APP.dropdownOptions.push({label: "Favourite", mode: "fav"});
	APP.updateUserFav();
	APP.favids = sessionStorage.favids.split(',');
}


// render header
React.render(
	<Header />,
  	document.getElementById('header')
);

// render media list
React.render(
	<MediaContainer />,
  	document.getElementById('container')
);
