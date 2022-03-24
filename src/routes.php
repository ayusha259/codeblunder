<?php

    require_once ROOT . '/../src/middlewares.php';

    //User Routes
    
    #Create a new User
    $app->post("/api/users/register", "UserController:signup");
    
    #Login a User
    $app->post("/api/users/login","UserController:login");

    #Update Profile
    $app->post("/api/users/edit", "UserController:updateInfo")->add($auth);

    #Get a User
    $app->get("/api/users/getdetails","UserController:getdetails")->add($auth);

    #User Edit page
    $app->get("/api/users/edit", "UserController:editpagedetails")->add($auth);

    #GET some other user
    $app->get("/api/users/{username}", "UserController:getuser");

    #Get all Users
    $app->get("/api/users","UserController:getAllUsers");


    //Questions Route
    
    #Create a new Question
    $app->post("/api/questions","QuesController:create")->add($auth);
    $app->get("/api/questions/search","QuesController:getByTitle");
    
    #Upvote a question
    $app->put("/api/questions/upvote/{id}","QuesController:upvote")->add($auth);

    #Downvote a question
    $app->put("/api/questions/downvote/{id}","QuesController:downvote")->add($auth);
    
    #Get all the questions
    $app->get("/api/questions","QuesController:getall");

    #Get question by tags
    $app->get("/api/questions/tags", "QuesController:getByTags");

    #Get a question by id
    $app->get("/api/question/{id}", "QuesController:getquestion");
    

    #Test Route
    $app->get("/api", "UserController:index");
    

    
    //Answer Routes

    #Create a new Answer
    $app->post("/api/answers","AnsController:create")->add($auth);

    #Edit answer
    $app->put("/api/answers/edit", "AnsController:edit")->add($auth);

    #Get answer by answer id
    $app->get("/api/answers/id/{id}","AnsController:getById");

    #Upvote a answer
    $app->put("/api/answers/upvote/{id}","AnsController:upvote")->add($auth);

    #Downvote a answer
    $app->put("/api/answers/downvote/{id}","AnsController:downvote")->add($auth);

    #Mark answer a solution
    $app->put("/api/answers/marksolution/{id}", "AnsController:markSolution")->add($auth);

    #Getting all Replies 
    $app->get("/api/answers/replies/{id}","AnsController:getAnsReplies");

    #Posting a reply
    $app->post("/api/answers/replies","AnsController:postReply")->add($auth);


    // TAGS
    
    #get all tags
    $app->get("/api/tags","TagController:getAllTags"); 


