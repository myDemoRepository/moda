<?php
class ProjectController extends Zend_Controller_Action
{
    public function init() {
     
        
    }
    
    
    function indexAction() 
    {
        // 
        switch ($_GET['act']) 
       {
            case 'myprojects':
                $this->myprojectsAction();
                break;
            case 'concurs':
                $this->concursAction();
                break;
            case 'fond':
                $this->fondAction();
                break;
            case 'bisnes':
                $this->bisnesAction();
                break;
            case 'new':
                $this->newAction();
                break;
            case 'projects':
                $this->projectsAction();
                break;
            case 'requests':
                $this->projectsrequestsAction();
                break;
            case 'deleted':
                $this->projectsdeletedAction();
                break;
            default :
                //$this->render('index');
                $this->projectsAction();
                break;
       }   
    }
    
    // закладка с моими удаленными проектами
    function projectsdeletedAction()
    {
        $project_members = new Application_Model_ProjectsMembers();
        $project = new Application_Model_Projects();
        $projects = $project->showalldeleted(); // вывод всех моих удаленных проектов
        $user = new Application_Model_User();
        
        foreach($projects as $k => $v)
        {
            // заказчик
            $users = $user->userinfo($v['requestor_id']);
            $projects[$k]['requestor_name'] = $users->name;
            $projects[$k]['requestor_family'] = $users->family;
            $projects[$k]['requestor_lvl'] = $users->lvl;
            $projects[$k]['img_url'] = $users->img_url;
            // учасники
            if ($v['members_id'] != "")
            {
                $projects[$k]['count_members'] = substr_count($v['members_id'], ';');
                $members = str_replace(";",',',$v['members_id']);
                $projects[$k]['members'] = $user->showmypeople($members);
            }
        }
        
        $this->view->projects = $projects;
        $this->render('projectsdeleted');
    }
    
    // помошник нотификации по емаил
    function notification_project_helper_email($type,$members_str,$project_id, $project_name)
    {
        if($members_str != 0)
        {    
            $members_mas = explode(';',$members_str);
            $members_mas[] = Zend_Auth::getInstance()->getIdentity()->id;
        }   
        else
            $members_mas = array(Zend_Auth::getInstance()->getIdentity()->id);
        //Оповещение на почту
        $notification = new Application_Model_Notification();
        // email нотификация
        $data_notification['name'] = $project_name;
        $data_notification['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
        $data_notification['project_id'] = $project_id;
        foreach($members_mas as $k => $v)
        {
            $notification->send($type, $v, $data_notification);
        }
        
    }
    
    //помошник нотификации по сайту
    function notification_project_helper_sayt($type_str,$members_str,$project_id,$requestor_id,$name)
    {
        $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
        $user  = new Application_Model_User();    
        $requestor_info = $user->userinfo($cur_id,"low"); // заказчик
        $notificationNode = new NotificationNode();
        $type = "project";
        $user_href[0] = $requestor_info['name']." ".$requestor_info['family']; // заказчик
        $user_href[1] = $name; // имя проекта
        $user_href[2] = $cur_id; // заказчик
        $img_arr = $requestor_info['img_url']; // заказчик
        $universal_id = $project_id;
        // если проект редактрирует учасник то не отсылать ему уведомления
        if($members_str != 0)
        {    
            $members_mas = explode(';',$members_str);
            $members_mas[] = $requestor_id;
        }   
        else // нет учасников
            $members_mas = array($requestor_id); 
        foreach($members_mas as $k => $val)
        {
            if($val != $cur_id)
            {
                $member_info = $user->userinfo($val,"low"); // заказчик
                $user_arr[] = $val;
                $lang = $member_info['lang'];
                $header_const[] = constant('PROJECT_'.strtoupper($lang));
                $data_text[] = constant('PROJECT_'.$type_str.'_'.strtoupper($lang));
                $user_href[3][] = 'current_members';
                $user_href[4][] = 'project_delete';
            }
        }
        // нотификация через node сервер
        if(count($members_mas)>1)
            $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$data_text,$user_href,$universal_id);
    }
    
    // удалить проект
    function flagupdateAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $project          = new Application_Model_Projects();
            $project_members  = new Application_Model_ProjectsMembers();
            $project_tasks    = new Application_Model_ProjectsTasks();
            $project_requests = new Application_Model_ProjectsRequests();
            $tasks            = new Application_Model_Tasks();
            
            $project_id = (int)$this->getRequest()->getPost('project_id');
            $mark = (int)$this->getRequest()->getPost('mark');
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            
            // информация по текущему пользователю
            $result_check_member = $project_members->membersinfolow($project_id, $cur_id);
            $result = $project->showcur($project_id);
            
            if($result['requestor_id'] == $cur_id || $result_check_member['rights_id'] == '0')
            {
                if($mark == 1) // восстановить проект
                {
                    // projects flag 
                    $project->flagupdate($project_id,1);
                    // project_members
                    $project_members->flagupdate($project_id,1);
                    // project_requests
                    $project_requests->flagupdate($project_id,1);
                   
                    // нотификация по email
                    $this->notification_project_helper_email('project_resurect',$result['members_id'],$project_id,$result['name']);
                    
                    // нотификация по sayt
                    $this->notification_project_helper_sayt('RESURECT',$result['members_id'],$project_id,$result['requestor_id'],$result['name']);
                    
                }
                else // удалить проект
                {
                
                    // Удалить проект можно при отсутствии задач либо всех выполненых задачах
                    $tasks_mas = explode(';',$result['tasks_id']);
                    if($tasks_mas[0] != '0')
                    {
                        $tasks_str = str_replace(';', ',', $result['tasks_id']);
                        // узнаеть есть ли невыполненые задачи
                        $tasks_info = $tasks->tasklistinfo($tasks_str);
                        $mark = 0; // нет задач
                        foreach($tasks_info as $k => $v)
                        {
                            if($v['status'] == 2)
                                $mark++; // есть выполненая задача
                        }
                        if($mark == count($tasks_info))
                        {
                            // projects flag 
                            $project->flagupdate($project_id,2);
                            // project_members
                            $project_members->flagupdate($project_id,2);
                            // project_requests
                            $project_requests->flagupdate($project_id,2);
                            
                            // нотификация по email
                            $this->notification_project_helper_email('project_delete',$result['members_id'],$project_id,$result['name']);
                            
                            // нотификация по sayt
                            $this->notification_project_helper_sayt('DELETE',$result['members_id'],$project_id,$result['requestor_id'],$result['name']);
                        }
                        else 
                            die (Zend_Json::encode(array('status' => 2))); 
                    }
                    else
                    {
                        // projects
                        $project->flagupdate($project_id,2);
                        // projects_members
                        $project_members->flagupdate($project_id,2);
                        // project_requests
                        $project_requests->flagupdate($project_id,2);
                        
                        // нотификация по email
                        $this->notification_project_helper_email('project_delete',$result['members_id'],$project_id,$result['name']);
                        
                        // нотификация по sayt
                        $this->notification_project_helper_sayt('DELETE',$result['members_id'],$project_id,$result['requestor_id'],$result['name']);
                        
                    }
                }
            }
            
            die (Zend_Json::encode(array('status' => 1))); 
        }
    }
    
    // смена роли учасника проекта (заказчиком)
    function changemembersroleAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            
            $project_id = (int)$this->getRequest()->getPost('project');
            $member_id = (int)$this->getRequest()->getPost('member');
            $member_val = (int)$this->getRequest()->getPost('member_r');
            
            $project_members = new Application_Model_ProjectsMembers();
            $project = new Application_Model_Projects();
            
            $project_cur = $project->showcur($project_id);
            
            // проверяем права пользователя 
            $result_check = $project_members->membersinfolow($project_id, $curid);
            
            // если заказчик или имеет полные права
            if($project_cur['requestor_id'] == $curid || $result_check['rights_id'] == 0)
            {
               
               $data['role_id'] = $member_val;
               $project_members->updatemember($data,$project_id,$member_id);
                
               
               die (Zend_Json::encode(array('status' => 1))); 
            }
            
            
        }
    }
    
    // смена прав учасника проекта (заказчиком)
    function changemembersrightAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            
            $project_id = (int)$this->getRequest()->getPost('project');
            $member_id = (int)$this->getRequest()->getPost('member');
            $member_val = (int)$this->getRequest()->getPost('member_r');
            
            $project = new Application_Model_Projects();
            $project_members = new Application_Model_ProjectsMembers();
            
            $project_cur = $project->showcur($project_id);
            
            // проверяем права пользователя 
            $result_check = $project_members->membersinfolow($project_id, $curid);
            
            // если заказчик или имеет полные права
            if($project_cur['requestor_id'] == $curid || $result_check['rights_id'] == 0)
            {
               $data['rights_id'] = $member_val;
               $project_members->updatememberrights($data,$project_id,$member_id);
               
               
               die (Zend_Json::encode(array('status' => 1))); 
            }
            
            
        }
    }
    
    
    // роли учасников проекта
    function membersrolesAction()
    {
        $id = (int)$this->_getParam('id');
        
        $curid = Zend_Auth::getInstance()->getIdentity()->id;
        $projects = new Application_Model_Projects();
        $project_info = $projects->showcur($id);
        $this->view->projectinfo = $project_info;
        
        // возможность редактирования только на живом проекте
        if($project_info['flag'] == 1)
           $this->view->project_flag = 1;
        else
           $this->view->project_flag = 2;
        
        if($project_info['requestor_id'] == $curid)
            $this->view->rmark = 1;
        
        $members = new Application_Model_ProjectsMembers();
        $members_info = $members->membersinfo($id);
        
        // информация по текущему пользователю
        $result_check = $members->membersinfolow($id, $curid);
        $this->view->member_info = $result_check;
        
        //все роли пользователей
        $role = new Application_Model_ProjectsMembersRole();
        $this->view->roles_info = $role->showall();
        
        $this->view->membersinfo = $members_info;
        $this->view->project = $id;
        
        
        
         if(count($result_check) > 0)
        {
            // обычный пользователь
            if($curid != $project_info['requestor_id'] && $result_check['status'] != 1 && $result_check['status'] != 2)
               $user_information = 2;
            // неподтвержденный учасник
            if ( $result_check['status'] == 1)
                $user_information = 0;
            // подтвержденный учасник
            if ( $result_check['status'] == 2)
                $user_information = 1;
        }
        // создатель
        if($curid == $project_info['requestor_id'])
            $user_information = 3;
      
        // Приватность проекта (1.4.7 - статусы проекта; 1 - приват,2 - шапка,3 - все)
        $switch_mas = array(
            '1' => array(
                3 , 3 , 3 , 3
            ),
            '4' => array(
                2 , 3 , 1 , 3     
            ),
            '7' => array(
                1 , 1 , 1 , 3
            )
        );
        
        $switch = $switch_mas[$project_info['status']][$user_information]; 
        
        if($switch == 3)
            $this->render('member-roles');
        else
            $this->_redirect('project');
                
    }
    
    // дерево команды
    function memberstreeAction()
    {
        $project_id = (int)$this->_getParam('id');
        
        $this->view->project = $project_id;
        $this->render('member-tree');
    }
    
    
    // учасники проекта
    function membersAction()
    {
        
        $id = (int)$this->_getParam('id');
        $curid = Zend_Auth::getInstance()->getIdentity()->id;
        $projects = new Application_Model_Projects();
        $project_info = $projects->showcur($id);
        $this->view->projectinfo = $project_info;
        $this->view->projectid = $id;
        
        // показывать возможность редактирования только для живых проектов
        
        if($project_info['flag'] == 1)
           $this->view->project_flag = 1;
        else
           $this->view->project_flag = 2;
            
        if($project_info['requestor_id'] == $curid)
            $this->view->rmark = 1;
        
        $members = new Application_Model_ProjectsMembers();
        $members_info = $members->membersinfo($id);
        
        // информация по текущему пользователю
        $result_check = $members->membersinfolow($id, $curid);
        $this->view->member_info = $result_check;
        
        //все роли пользователей
        $role = new Application_Model_ProjectsMembersRole();
        $this->view->roles_info = $role->showall();
        
        if(count($members_info)>0)
        {
            $tasks = new Application_Model_Tasks();
            
            foreach($members_info as $k=>$v)
            {
                $task = $tasks->getTaskInProjectById($v['u_id'],$id);
                $str = '';
                $members_info[$k]['task_count'] = count($task);
                if(count($task)>0)
                {
                    
                    $last_m = array_pop($task);
                    foreach($task as $key=>$val)
                    {
                        $str .= "<a href='/task-".$val['id']."/' target='_blank'>".$val['task_name']."</a> ,<br> ";
                    }
                    $str .= "<a href='/task-".$last_m['id']."/' target='_blank'>".$last_m['task_name']."</a>";
                }
                $members_info[$k]['task_name'] = $str;
                
                // указываем роль каждого учасника
                
                
            }
        }
        
       // die(var_dump($result_check));
        
        $this->view->membersinfo = $members_info;
        
        
        if(count($result_check) > 0)
        {
            // обычный пользователь
            if($curid != $project_info['requestor_id'] && $result_check['status'] != 1 && $result_check['status'] != 2)
               $user_information = 2;
            // неподтвержденный учасник
            if ( $result_check['status'] == 1)
                $user_information = 0;
            // подтвержденный учасник
            if ( $result_check['status'] == 2)
                $user_information = 1;
        }
        // создатель
        if($curid == $project_info['requestor_id'])
            $user_information = 3;
      
        // Приватность проекта (1.4.7 - статусы проекта; 1 - приват,2 - шапка,3 - все)
        $switch_mas = array(
            '1' => array(
                3 , 3 , 3 , 3
            ),
            '4' => array(
                2 , 3 , 1 , 3     
            ),
            '7' => array(
                1 , 1 , 1 , 3
            )
        );
        
        $switch = $switch_mas[$project_info['status']][$user_information]; 
        
        if($switch == 3)
            $this->render('members');
        else
            $this->_redirect('project');
                        
    }
    
    //
    function myprojectsAction()
    {
        $project = new Application_Model_ProjectsRequests();
        $projects = $project->showmy();
        $user = new Application_Model_User();
        
        if(count($projects)>0)
        {    
            foreach($projects as $k => $v)
            {
                // заказчик
                $users = $user->userinfo($v['requestor_id']);
                $projects[$k]['requestor_name'] = $users->name;
                $projects[$k]['requestor_family'] = $users->family;
                $projects[$k]['requestor_lvl'] = $users->lvl;
                
                // учасники
                if ($v['members_id'] != "")
                {
                    $members = str_replace(";",',',$v['members_id']);
                    $projects[$k]['members'] = $user->showmypeople($members);
                }
            }
        }
        
        $this->view->projects = $projects;
        $this->render('myprojects');
        
        
        
    }
    
    
    
    // заявки // согласиться/1/ отказаться/2/ участвовать в проекте
    function requestsAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $mark = $this->getRequest()->getPost('mark');
            $project_id = $this->getRequest()->getPost('project_id');
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            
            $project_log = new Application_Model_ProjectsLog();
            // работа с таблицей заявок
            $requests = new Application_Model_ProjectsRequests();
            if($mark == 1)
                $data = array('status' => 2); // согласен участвовать 
            if($mark == 2)
                $data = array('status' => 3);  // отказаться от участия
            $request = $requests->update($data,$project_id);
            
            // работа с таблицей учасников
            $members = new Application_Model_ProjectsMembers();
            if($mark == 1)
            {
                $data = array('status' => 2); // подтвердил участие 
                $member = $members->update($data, $project_id);
                $projects = new Application_Model_Projects();
                $project = $projects->showcur($project_id);
                $type_notification = 'project_member_accept';
                //user_take_project
                //добавление в лог проекта подтверждение участия
                $data = array(
                   'project_id' => $project_id,
                   'date' => time(),
                   'field_name' => 'Участие в проекте',
                   'old_value' => 'Подтвердил',
                   'new_value' => $cur_id,
                   'user_id' => $cur_id
                );
                $project_log->insert($data); 
                
            }
            if($mark == 2)
            {   
                $type_notification = 'project_member_refused';
                //user_refuse_project
                $projects = new Application_Model_Projects();
                $project = $projects->showcur($project_id);
                $members_str = $project['members_id'];
                $count = substr_count($members_str, ";");
                if ($count == 0)
                    $members_str = str_replace($cur_id, '0', $members_str);
                else
                {
                    $members_str = str_replace($cur_id, '', $members_str);
                    $members_str = str_replace(";;", ';', $members_str);
                    if(substr($members_str, -1) == ";") // если последний символ ";"
                        $members_str = substr($members_str,0,-1);
                    if(substr($members_str, 0,1) == ";") // если первый символ ";"
                        $members_str = substr($members_str,1);
                }    
               
                // удалить учасника из таблицы project_members
                $member = $members->delete($project_id); 
                // удалить учасника из таблицы projects
                
                $data = array('members_id' => $members_str);
                $projects->update($data, $project_id);
                
                
                //добавление в лог проекта отклонения участия
                $data = array(
                   'project_id' => $project_id,
                   'date' => time(),
                   'field_name' => 'Участие в проекте',
                   'old_value' => 'Отказался',
                   'new_value' => $cur_id,
                   'user_id' => $cur_id
                );
                $project_log->insert($data); 
            }
            
            $user = new Application_Model_User();
            $notification = new Application_Model_Notification();
            $user_info = $user->fetchAllbyId($cur_id);
            
            $data_notification = array(
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/',
                'project_id' => $project_id,
                'project_name' => $project['name'],
                'user_id' => $cur_id,
                'user_name' => $user_info[0]['name'].' '.$user_info[0]['family']
            );
            
            $notification->send($type_notification, $project['requestor_id'], $data_notification);
            
            ////////////////////////////////////////////////////////////////////
            // отсылаем на Ноду уведомление
            //////////////////////////////////////////////////
            $user_info = $user->userinfo($cur_id,"low");
            $requestor_info = $user->userinfo($project['requestor_id'],"low");
            $notificationNode = new NotificationNode();
            $type = "project";
            //определяем язык 
            $lang = $requestor_info['lang']; // на языке получателя
            $user_arr[] = $project['requestor_id'];
            
            if ($mark == 1) // согласился на проект
            {    
                $post[] = constant('PROJECT_CONFIRM_'.strtoupper($lang));
                $user_href[3][] = '';
                $user_href[4][] = 'project_member_accept';
            }   
            if ($mark == 2) // отказался от проекта
            {    
                $post[] = constant('PROJECT_REFUSED_'.strtoupper($lang));
                $user_href[3][] = '';
                $user_href[4][] = 'project_member_refused';
            }   
            
            $header_const[] = constant('PROJECT_'.strtoupper($lang));
               
            $user_href[0] = $user_info['name']." ".$user_info['family']; // подающий заявку
            $user_href[1] = $project['name']; // имя проекта
            $user_href[2] = $cur_id; // подающий заявку
            
            
            $img_arr = $user_info['img_url']; // подающий заявку
            $universal_id = $project_id;
            // нотификация через node сервер
            $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$post,$user_href,$universal_id);
          
            die (Zend_Json::encode(array('status' => 0)));
        }
        
    }
    
    // переход между моими заявками в проектах
    function myrequestsAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $mark = (int)$this->getRequest()->getPost('r_id'); // 1 текущие  /2 выполненые /3 просроченые
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            
            $request = new Application_Model_ProjectsRequests();
            $requests = $request->memberrequests($mark);
            $this->view->requests = $requests;
            $this->view->mark = $mark;
            $html = $this->view->render('project/requests-current.phtml');
            
            die (Zend_Json::encode(array('status' => 1,'html' => $html)));
        }
    }
    
    // мои заявки на проекты
    function projectsrequestsAction()
    {
        if(!Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('auth/login');
        
        $request = new Application_Model_ProjectsRequests();
        $requests = $request->memberrequests(1);
        $requests_mas = $request->requestscount();
        
        $this->view->count_requests = $requests_mas;
        $this->view->requests = $requests;
        $this->render('requests');
    }
    
    // подтверждаем участие пользователя в проекте
    function addmemberrequestAction()
    {
        
        if($this->getRequest()->isXmlHttpRequest())
        {
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            $project_id = (int)$this->getRequest()->getPost('project_id');
            $member_id =(int)$this->getRequest()->getPost('memberid');
            
            $project = new Application_Model_Projects();
            $projects_members = new Application_Model_ProjectsMembers();
            $project_requests = new Application_Model_ProjectsRequests();
            
            // получаем данные о проекте
            $projects = $project->showcur($project_id);
            
            // запись в лог проектов
            if($projects['members_id'] == 0)
                $members_ids = $member_id;
            else
                $members_ids = $projects['members_id'].";".$member_id;
            
            //добавление в лог проекта учасников
            $project_log = new Application_Model_ProjectsLog();
            $data = array(
                'project_id' => $project_id,
                'date' => time(),
                'field_name' => 'Учасники проекта',
                'old_value' => $projects['members_id'],
                'new_value' => $members_ids,
                'user_id' => $cur_id
            );
            $project_log->insert($data); 
            
            $data = array(
                'project_id' => $project_id,
                'date' => time(),
                'field_name' => 'Участие в проекте',
                'old_value' => 'Подтвердил',
                'new_value' => $member_id,
                'user_id' => $cur_id
            );
            $project_log->insert($data); 
            
            // project_members insert
            $data = array(
               'member_id' => $member_id,
               'project_id' => $project_id,
               'status' => 2 
            );
            $projects_members->insert($data);
            
            //project_requests update
            $data = array(
                'status' => 2,
                'request_reg' =>time()
            );
            $project_requests->update($data, $project_id, $member_id);
            
            // прописать в проект
            $old_project = $project->showcur($project_id);
            if($old_project['members_id'] != 0)
            {    
                $data = array(
                    'members_id' => $old_project['members_id'].";".$member_id
                );
            }
            else
            {
                $data = array(
                    'members_id' => $member_id
                );
            }
            $project->update($data, $project_id);
            
            $notification = new Application_Model_Notification();
            
            $data_notification = array(
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/',
                'project_id' => $project_id,
                'project_name' => $projects['name']
            );
            
            $notification->send('project_add_request_accept', $member_id, $data_notification);
            //author_take_request
            ////////////////////////////////////////////////////////////////////
            // отсылаем на Ноду уведомление
            //////////////////////////////////////////////////
            $user = new Application_Model_User();
            $user_info = $user->userinfo($cur_id,"low");
            $requestor_info = $user->userinfo($member_id,"low");
            $notificationNode = new NotificationNode();
            $type = "project";
            //определяем язык 
            $lang = $requestor_info['lang']; // на языке получателя
            $user_arr[] = $member_id;
            
            $post[] = constant('PROJECT_ACCEPT_'.strtoupper($lang));
            $header_const[] = constant('PROJECT_'.strtoupper($lang));
               
            $user_href[0] = $user_info['name']." ".$user_info['family']; // подающий заявку
            $user_href[1] = $projects['name']; // имя проекта
            $user_href[2] = $cur_id; // подающий заявку
            $user_href[3] = '';
            $img_arr = $user_info['img_url']; // подающий заявку
            $universal_id = $project_id;
            // нотификация через node сервер
            $user_href[4][] = 'project_add_request_accept';
            $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$post,$user_href,$universal_id);
            
            
            die(Zend_Json::encode(array('status' => 1)));
        }
    }
    
    // оставляем заявку на проект
    function addprojectrequestAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {     
            
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            $project_id = (int)$this->getRequest()->getPost('project-id');
            $request_text = htmlspecialchars($this->getRequest()->getPost('project-text-request'));
            $request_mark = $this->getRequest()->getPost('request-name');
            $request_count = $this->getRequest()->getPost('requestcount');
            $href_portfolio = htmlspecialchars($this->getRequest()->getPost('href_portfolio'));
            
            if($request_mark == 1)
                $status = 5;
            else 
                $status = 4;
            
            $projects_requests = new Application_Model_ProjectsRequests();
            $ok = $projects_requests->checkmember($curid, $project_id,'1');
            if($ok == 1)// изменить статус с на $status
            {
                $data = array(
                    'status' => $status,
                    'request_reg' => time(),
                    'text' => $request_text
                    
                    );    
                $projects_requests->update($data, $project_id);
            }           
            else // добавить его в projects_requests со статусом $status
            {
                $data = array(
                    'member_id' => $curid,
                    'project_id' => $project_id,
                    'status' => $status,
                    'request_reg' => time(),
                    'text' => $request_text,
                    'href_portfolio' => $href_portfolio
                    );    
                $projects_requests->insert($data);
            }
            
            //добавление в лог проекта учасников
            $project_log = new Application_Model_ProjectsLog();
            $data = array(
                'project_id' => $project_id,
                'date' => time(),
                'field_name' => 'Участие в проекте',
                'old_value' => 'Подал заявку',
                'new_value' => $curid,
                'user_id' => $curid
            );
            $project_log->insert($data); 
            
            $user = new Application_Model_User();
            $project = new Application_Model_Projects();
            $user_info = $user->userinfo($curid,'low');
            $project_info = $project->showcur($project_id,'');
            
            $data_notification = array(
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/',
                'project_id' => $project_id,
                'project_name' => $project_info['name'],
                'user_name' => $user_info['name'].' '.$user_info['family'],
                'user_id' => $curid,
                'request_text' => $request_text
            );
            
            $notification = new Application_Model_Notification();
            $notification->send('project_add_request', $project_info['requestor_id'], $data_notification);
            //user_send_request_project
            $this->view->count = $request_count;
            $this->view->text = $request_text;
            $this->view->href = $href_portfolio;
            $this->view->date = time();
            $this->view->requests_show = $user_info;
            
            $html = $this->view->render('project/request-my.phtml');
            
            ////////////////////////////////////////////////////////////////////
            // отсылаем на Ноду уведомление
            //////////////////////////////////////////////////
            $requestor_info = $user->userinfo($project_info['requestor_id'],"low");
            $notificationNode = new NotificationNode();
            $type = "project";
            //определяем язык 
            $lang = $requestor_info['lang']; // на языке получателя
            $user_arr[] = $project_info['requestor_id'];
            
            $post[] = constant('PROJECT_REQUEST_'.strtoupper($lang));
            $header_const[] = constant('PROJECT_R_'.strtoupper($lang));
               
            $user_href[0] = $user_info['name']." ".$user_info['family']; // подающий заявку
            $user_href[1] = $project_info['name']; // имя проекта
            $user_href[2] = $curid; // подающий заявку
            $user_href[3] = '';
            $img_arr = $user_info['img_url']; // подающий заявку
            $universal_id = $project_id;
            // нотификация через node сервер
            $user_href[4][] = 'project_add_request';
            $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$post,$user_href,$universal_id);
            
            die (Zend_Json::encode(array('status' => 1, 'html' => $html)));
        }
    }
    
    // выводим страницу проекта
    function projectAction()
    {
        if(isset($_GET['p']))
           $project_id = (int)$_GET['p'];
        else
           $project_id = (int)$this->_getParam('id');
        $access = 0;
        
        
        $project = new Application_Model_Projects();
        $project_members = new Application_Model_ProjectsMembers();
        $project_requests = new Application_Model_ProjectsRequests();
        $projects = $project->showcur($project_id);
        
        $curid = Zend_Auth::getInstance()->getIdentity()->id;
        $user = new Application_Model_User();
        $task = new Application_Model_Tasks();
        $this->view->projectid = $project_id;
        $this->view->curid = $curid;
        $this->view->flag_mark = $projects['flag'];
        
        if(!is_null($projects))
        { 
            // заказчик
            $users = $user->userinfo($projects['requestor_id']);
            $projects['requestor_name'] = $users->name;
            $projects['requestor_family'] = $users->family;
            $projects['requestor_lvl'] = $users->lvl;
            
            // учасники
            if ($projects['members_id'] != "")
            {
                $members = str_replace(";",',',$projects['members_id']);
                $projects['members'] = $user->showmypeople($members);
                
            
                foreach($projects['members'] as $k => $v)
                {
                      
                    $status = $project_members->showstatus($project_id,$v['id']);
                    $projects['members'][$k]['members_status'] = $status['status'];
                       
                    if($status['status'] == 2)
                    {    
                           
                        $task_href = "";
                        $status_current = $task->currentprojecttasks($project_id,$v['id']);
                          
                        if(count($status_current)>0)
                        {
                            foreach($status_current as $key=>$val)
                            {
                                $task_href .= "<a href='/task-".$val['id']."/' target='_blank'>".$val['task_name']."</a>,";
                            }
                            $task_href = substr($task_href, 0,-1);
                            $projects['members'][$k]['curent_tasks'] = $task_href;
                        }
                        else
                        {
                            $projects['members'][$k]['curent_tasks'] = 'нет задач';
                        }
                        
                    }
                    else
                        $projects['members'][$k]['curent_tasks'] = 'нет задач';
                }
                    
            }
        }
        // показать историю создателю или учаснику
        if ($projects['members_id'] != 0)
        {
            $projects['members_id'] = str_replace(";", ",", $projects['members_id']);
            $memberslist = $project_members->checkmemberslist($projects['members_id'], $project_id, '2');
            foreach($memberslist as $k=>$v)
            {
                $memberslist_true[$k] = $v['member_id'];
            }    
            if(count($memberslist_true) == 0)
                $memberslist_true = array();
            if (in_array($curid, $memberslist_true))
                $param = 1;
            else 
                $param = 0;
        }
        // проверить есть ли заявка учасника со статусом 1
        $request = $project_requests->checkmember($curid, $project_id, '8');
        if ($request == 1)
            $this->view->personal_mark = 1;
        else
            $this->view->personal_mark = 0;
        // показать возможность оставить заявку 
        // только не подтвержденным учасникам и не заказчику и нет уже оставленых заявок
        $ok = $project_requests->checkmemberstatus($curid, $project_id,'1,2,4,5'); 
        if ($ok == 1 && $projects['requestor_id'] != $curid)
            $this->view->request_mark = 1; // показать форму заявки
        else
           $this->view->request_mark = 0;
        // показать заявки пользователей
        //if ($this->view->request_mark == 0)
        if ($param == 1 || $projects['requestor_id'] == $curid)
            $requests = $project_requests->memberrequests('4,5',$project_id,'2');
        else 
            $requests = $project_requests->memberrequests('4',$project_id,'3');
//var_dump($requests);
        $this->view->requests_show = $requests;
        // показывать историю заказчику и подтвержденным учасникам
        if($param == 1 || $projects['requestor_id'] == $curid)
            $this->view->history_mark = 1;   
        else    
            $this->view->history_mark = 0; 
        // разрешить редактировать если проект мой
        if ($projects['requestor_id'] == $curid)
            $this->view->editon = 1;
        else 
            $this->view->editon = 0;
        // информация по текущему пользователю
        $result_check = $project_members->membersinfolow($project_id, $curid);
        $this->view->member_info = $result_check;
        
        // показать задачи привязаные к проекту
        $tasks_mass = $task->showProjectTask(str_replace(';',',',$projects['tasks_id']),$project_id);
        if(count($tasks_mass)>0)
        {
            
            $data = array();
            $price = 0;
            $tasks_allowed = array();
            foreach($tasks_mass as $k=>$v)
            {
                // пользователь создатель проекта или подтвержденный учасник с полным доступом (видит все задачи) 
                if($projects['requestor_id'] == $curid || ($result_check['status'] == 2 && $result_check['rights_id'] == '0'))
                {
                    // массив задач для отображения в табличке задач
                    $tasks_allowed[] = $v;
                    // метка для отображения диаграммы ганта
                    $gant_mark = 1;
                    $deny_mark = 1;
                    // cобераем строку разрешеных для показа задач
                    $tasks_str .= $v['id'].',';
                    // валюта задачи
                    switch ($v['price_valute'])
                    {
                        case 1:
                            $tasks_mass[$k]['price_valute_convert'] = 'руб.';
                            break;
                        case 2:
                            $tasks_mass[$k]['price_valute_convert'] = 'usd';
                            break;
                        case 3:
                            $tasks_mass[$k]['price_valute_convert'] = 'euro';
                            break;
                    }
                    $html = "";
                    $price+=$v['price'];
                    // для диаграммы ганта
                    if($v['date_start'] == 0)
                        $date_start = $v['date_reg'];
                    else
                        $date_start = $v['date_start'];
                    $this->view->task_x = $v['id'];
                    $this->view->task_name = $v['task_name'];
                    $this->view->task_content = $v['task_content'];
                    $this->view->task_price = $v['price'];
                    $this->view->taskpricevalute = $tasks_mass[$k]['price_valute_convert'];
                    $this->view->date_end = $v['date_end'];
                    // верстка всплывающего окна на диаграмме ганта
                    $html = $this->view->render('project/ganttaskinfo.phtml');
                    $data[] = array(
                       'label' => $v['task_name'],
                       'start' => date("Y-m-d",$date_start), 
                       'end'   => date("Y-m-d",$v['date_end']),
                       'task_id' => $v['id'],
                       'task_name' => $v['task_name'],
                       'html' => $html
                    );
                }
                
                
                // пользователь подтвержденный учасник то он видит все задачи со статусом 2 
                if($result_check['status'] == 2 && $deny_mark != 1)
                {
                    if($v['t_status'] == 2)
                    {   
                        // массив задач для отображения в табличке задач
                        $tasks_allowed[] = $v;
                        // метка для отображения диаграммы ганта
                        $gant_mark = 1;
                        // cобераем строку разрешеных для показа задач
                        $tasks_str .= $v['id'].',';
                        // валюта задачи
                        switch ($v['price_valute'])
                        {
                            case 1:
                                $tasks_mass[$k]['price_valute_convert'] = 'руб.';
                                break;
                            case 2:
                                $tasks_mass[$k]['price_valute_convert'] = 'usd';
                                break;
                            case 3:
                                $tasks_mass[$k]['price_valute_convert'] = 'euro';
                                break;
                        }
                        $html = "";
                        $price+=$v['price'];
                        // для диаграммы ганта
                        if($v['date_start'] == 0)
                            $date_start = $v['date_reg'];
                        else
                            $date_start = $v['date_start'];
                        $this->view->task_x = $v['id'];
                        $this->view->task_name = $v['task_name'];
                        $this->view->task_content = $v['task_content'];
                        $this->view->task_price = $v['price'];
                        $this->view->taskpricevalute = $tasks_mass[$k]['price_valute_convert'];
                        $this->view->date_end = $v['date_end'];
                        // верстка всплывающего окна на диаграмме ганта
                        $html = $this->view->render('project/ganttaskinfo.phtml');
                        $data[] = array(
                           'label' => $v['task_name'],
                           'start' => date("Y-m-d",$date_start), 
                           'end'   => date("Y-m-d",$v['date_end']),
                           'task_id' => $v['id'],
                           'task_name' => $v['task_name'],
                           'html' => $html
                        );
                    }
                }
                
                // пользователь исполнитель или создатель или наблюдатель задачи но не учасник и не создатель проекта 
                if( ( $v['requestor_id'] == $curid || $v['performer_id'] == $curid || $v['observer_id'] == $curid ) && $deny_mark != 1 )
                {
                    if($v['t_status'] == 1)
                    {   
                        // массив задач для отображения в табличке задач
                        $tasks_allowed[] = $v;
                        // метка для отображения диаграммы ганта
                        $gant_mark = 1;
                        // cобераем строку разрешеных для показа задач
                        $tasks_str .= $v['id'].','; 
                        // валюта задачи
                        switch ($v['price_valute'])
                        {
                            case 1:
                                $tasks_mass[$k]['price_valute_convert'] = 'руб.';
                                break;
                            case 2:
                                $tasks_mass[$k]['price_valute_convert'] = 'usd';
                                break;
                            case 3:
                                $tasks_mass[$k]['price_valute_convert'] = 'euro';
                                break;
                        }
                        $html = "";
                        $price+=$v['price'];
                        // для диаграммы ганта
                        if($v['date_start'] == 0)
                            $date_start = $v['date_reg'];
                        else
                            $date_start = $v['date_start'];
                        $this->view->task_x = $v['id'];
                        $this->view->task_name = $v['task_name'];
                        $this->view->task_content = $v['task_content'];
                        $this->view->task_price = $v['price'];
                        $this->view->taskpricevalute = $tasks_mass[$k]['price_valute_convert'];
                        $this->view->date_end = $v['date_end'];
                        // верстка всплывающего окна на диаграмме ганта
                        $html = $this->view->render('project/ganttaskinfo.phtml');
                        $data[] = array(
                           'label' => $v['task_name'],
                           'start' => date("Y-m-d",$date_start), 
                           'end'   => date("Y-m-d",$v['date_end']),
                           'task_id' => $v['id'],
                           'task_name' => $v['task_name'],
                           'html' => $html
                        );
                    }
                }
                
            }
            
            $tasks_str = mb_substr($tasks_str, 0,-1,'utf8');
            $this->view->allowed_tasks = $tasks_str;
            
            if($gant_mark == 1)
            {
                
                $this->view->tasks = $tasks_allowed;
                $this->view->x_param = $x_param;
                if(isset($_COOKIE['project-'.$project_id]))
                    $cokie = $_COOKIE['project-'.$project_id];
                else
                    $cokie = 0;
                $gantti = new Gantti($data, array(
                    'title'      => 'Задачи',
                    'cellwidth'  => 35,
                    'cellheight' => 45,
                    'scale'      => $cokie 
                ));
                $this->view->gantti = $gantti;
            }
        }
        $this->view->price = $price;
      //  echo('<pre>');
      //  die(var_dump($xc));
        $mounthmass = array(1 =>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря');
        $curmounth = date("n",$projects['date_create']);
        $this->view->relize_date = date("d/m/Y",$projects['date_create']);
        //date("j",$projects['date_create'])." ".$mounthmass[$curmounth]." ".date("Y",$projects['date_create']);
        $this->view->terms = round($projects['term']/60/60/24/30);
        $this->view->projects = $projects;
        
        // статусы задач
        $tasks_status = $project->tasksstatus();
        
        foreach ($tasks_status as $k=>$v)
        {
            $result_task_status[$v['status']] = $v['name'];
        }
        $result_task_status[8] = "Все задачи";
        $this->view->tasksstatus = $result_task_status;
        
        
        // порядок отрисовки блоков
        if(isset($_COOKIE['project-order'.$project_id]))
        {
            $this->view->order_set = 1;
        }
        
        
      // МОЙ КОД!!!!!!!!!!
        $now      = time();
        $today    = strtotime("today 00:00");
        $messages = Library::get_messages(array(
                        'subject'=> $project_id,
                        'type'   => 'project',
                        'cols'   => 0,
                        'start'  => $today,
                        'end'    => $now
                 ));    
        if($messages['first_time'] > $today) // переписка только сегодня
            $time_range = 0;
        elseif($messages['first_time'] <= $today && $messages['first_time'] > ($today-(60*60*24))) // переписка за вчера 
            $time_range = 1;
        elseif($messages['first_time'] <= ($today-(60*60*24)) && $messages['first_time'] > ($now-(60*60*24*7)))
            $time_range = 2;
        elseif($messages['first_time'] <= ($now-(60*60*24*7)))
            $time_range = 3;
        $this->view->messages       = $messages['messages'];
        $this->view->count_mess     = $messages['count'];
        $this->view->user_info      = $messages['user_info'][$curid];//$user->userinfo($curid);
        $this->view->count_all_mess = $messages['count_all'];
        $this->view->time_range     = $time_range;
        $this->view->project_id     = $project_id;
     // КОНЕЦ МОЕГО КОДА !!!!!
        
        // обычный пользователь
        if($curid != $projects['requestor_id'] && $request != 1 && $messages['user_info'][$curid]['status'] != 2)
           $user_information = 2;
       
        // неподтвержденный учасник
        if ($request == 1)
            $user_information = 0;
        // подтвержденный учасник
        if ($messages['user_info'][$curid]['status'] == 2)
            $user_information = 1;
        // создатель
        if($curid == $projects['requestor_id'])
            $user_information = 3;
      
        // Приватность проекта (1.4.7 - статусы проекта; 1 - приват,2 - шапка,3 - все)
        $switch_mas = array(
            '1' => array(
                3 , 3 , 3 , 3
            ),
            '4' => array(
                2 , 3 , 1 , 3     
            ),
            '7' => array(
                1 , 1 , 1 , 3
            )
            // индексы соответствуют 0 - учасник неподтв, 1- учасник подтв, 2 - пользователь, 3 - создатель
        );
        
        $switch = $switch_mas[$projects['status']][$user_information]; 
        $this->view->switch = $switch;
        
        
        if($projects['flag'] == 2)
        {
            if($projects['requestor_id'] == $curid || ($result_check['status'] == 2 && $result_check['rights_id'] == '0'))
                $this->render('project');    
            else
                $this->_redirect('/project/');
        }
        else
            $this->render('project');    
            
        
    }
 
    // gant diagram
    function gantdiagramAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $width = $this->getRequest()->getPost('width');
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            
            $task_str = htmlspecialchars($this->getRequest()->getPost('task_ok'));
            
            $task = new Application_Model_Tasks();
            $project = new Application_Model_Projects();
            
            
            $mark = (int)$this->getRequest()->getPost('mark');
            //$project_id = (int)$this->getRequest()->getPost('project_id');
            
            //$projects = $project->showcur($project_id);
                
            // показать задачи привязаные к проекту
            $tasks_mass = $task->showProjectTask(str_replace(';',',',$task_str));
            if(count($tasks_mass)>0)
            {
                $data = array();
                $price = 0;
                foreach($tasks_mass as $k=>$v)
                {
                    // валюта задачи
                    switch ($v['price_valute'])
                    {
                        case 1:
                            $tasks_mass[$k]['price_valute_convert'] = 'руб.';
                            break;
                        case 2:
                            $tasks_mass[$k]['price_valute_convert'] = 'usd';
                            break;
                        case 3:
                            $tasks_mass[$k]['price_valute_convert'] = 'euro';
                            break;
                    }
                    $html = "";
                    $price+=$v['price'];
                    // для диаграммы ганта
                    if($v['date_start'] == 0)
                        $date_start = $v['date_reg'];
                    else
                        $date_start = $v['date_start'];
                    $this->view->task_x = $v['id'];
                    $this->view->task_name = $v['task_name'];
                    $this->view->task_content = $v['task_content'];
                    $this->view->task_price = $v['price'];
                    $this->view->taskpricevalute = $tasks_mass[$k]['price_valute_convert'];
                    $this->view->date_end = $v['date_end'];
                    $html = $this->view->render('project/ganttaskinfo.phtml');
                    $data[] = array(
                       'label'     => $v['task_name'],
                       'start'     => date("Y-m-d",$date_start), 
                       'end'       => date("Y-m-d",$v['date_end']),
                       'task_id'   => $v['id'],
                       'task_name' => $v['task_name'],
                       'html'      => $html
                       
                    );
                }
                $this->view->tasks = $tasks_mass;
                $gantti = new Gantti($data, array(
                    'title'      => 'Задачи',
                    'cellwidth'  => 35,
                    'cellheight' => 45,
                    'scale'      => $mark,
                    'width'      => $width
                ));
                $this->view->gantti = $gantti;
            }
            $this->view->price = $price;
            $html = $this->view->render('project/gansdiagramrefresh.phtml');
            die (Zend_Json::encode(array('status' => 1 , 'html' => $html)));
            
        }
    }
    
    // сортировака задач на странице проекта
    function relatedtasksAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $project_id = (int)$this->getRequest()->getPost('project_id');
            $sort_order = (int)$this->getRequest()->getPost('sort_status');
            
            $task = new Application_Model_Tasks();
            //$project = new Application_Model_Projects();
            //$projects = $project->showcur($project_id);
            
            $tasks_str = htmlspecialchars($this->getRequest()->getPost('task_ok'));
            
            // показать задачи привязаные к проекту сортированые
            if($sort_order != 8)
                $task_info = $task->showProjectTaskOrder(str_replace(';',',',$tasks_str),$sort_order);
            else
                $task_info = $task->showProjectTask(str_replace(';',',',$tasks_str));
            
            
            if(count($task_info)>0)
            {    
                foreach($task_info as $k=>$v)
                {
                    // валюта задачи
                    switch ($v['price_valute'])
                    {
                        case 1:
                            $task_info[$k]['price_valute_convert'] = 'руб.';
                            break;
                        case 2:
                            $task_info[$k]['price_valute_convert'] = 'usd';
                            break;
                        case 3:
                            $task_info[$k]['price_valute_convert'] = 'euro';
                            break;
                    }
                    $this->view->tasks = $task_info;
                }
            }
            
            $html = $this->view->render('project/relatedtasks.phtml');
            die (Zend_Json::encode(array('html' => $html)));
        }
    }
    
    
    // показать историю проекта
    function projecthistoryAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $project_id = (int)$this->getRequest()->getPost('project_id');
            $project_log = new Application_Model_ProjectsLog();
            $project = new Application_Model_Projects();
            $result = $project_log->showproject($project_id);
            $user = new Application_Model_User();
            
            foreach ($result as $key=>$val)
            {
                if($result[$key]['field_name'] == 'Задачи проекта')
                {
                    $task = new Application_Model_Tasks();
                    if($val['old_value'] == 0)
                        $result[$key]['task_mas_old'] = 0;
                    else
                    {    
                       $val['old_value'] = str_replace(";", ",", $val['old_value']);
                       $result[$key]['tasks_mas_old'] = $task->show($val['old_value'],'showtaskname');
                    }
                    
                    if($val['new_value'] == 0)
                        $result[$key]['task_mas_new'] = 0;
                    else
                    {    
                       $val['new_value'] = str_replace(";", ",", $val['new_value']);
                       $result[$key]['tasks_mas_new'] = $task->show($val['new_value'],'showtaskname');
                    }
                    
                }
                
                if($result[$key]['field_name'] == 'Учасники проекта')
                {
                    $user = new Application_Model_User();
                    if($val['old_value'] == 0)
                        $result[$key]['members_mas_old'] = 0;
                    else
                    {    
                        $val['old_value'] = str_replace(";", ",", $val['old_value']);
                        $result[$key]['members_mas_old'] = $user->showmypeople($val['old_value']);
                    }
                    
                    
                    if($val['new_value'] == 0)
                        $result[$key]['members_mas_new'] = 0;
                    else
                    {    
                       $val['new_value'] = str_replace(";", ",", $val['new_value']);
                       $result[$key]['members_mas_new'] = $user->showmypeople($val['new_value']);
                    }
                }
                
                if($result[$key]['field_name'] == 'Участие в проекте')
                {
                    $user = new Application_Model_User();
                   // if($val['old_value'] == 'Заявлен') 
                   // {
                        $result[$key]['member_info'] = $user->showmypeople($val['new_value']);
                   // }
                       
                }
                
                if($result[$key]['field_name'] == 'Приватность проекта')
                {
                   
                    switch($val['old_value'])
                    {
                       case 1:
                           $result[$key]['old_privacy'] = "Для всех";
                           break;
                       case 4:
                           $result[$key]['old_privacy'] = "Для учасников";
                           break;
                       case 7:
                           $result[$key]['old_privacy'] = "Для автора";
                           break;
                       
                    }
                   
                    switch($val['new_value'])
                    {
                       case 1:
                           $result[$key]['new_privacy'] = "Для всех";
                           break;
                       case 4:
                           $result[$key]['new_privacy'] = "Для учасников";
                           break;
                       case 7:
                           $result[$key]['new_privacy'] = "Для автора";
                           break;
                       
                    }
                       
                }
               
                
                // кто редактировал
                $result[$key]['user_edit_id'] = $val['user_id'];
                $user_edit = $user->userinfo($val['user_id']);
                $result[$key]['user_edit_name'] = $user_edit->name ;
                $result[$key]['user_edit_family'] = $user_edit->family;
            }
            
            
            $this->view->project = $result;
                    
            $this->view->project_info = $project->showcur($project_id);
            $mounthmass = array(1 =>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря');
            $curmounth = date("n",$this->view->project_info['date_create']);
            $this->view->datecreate = date("j",$this->view->project_info['date_create'])." ".$mounthmass[$curmounth]." ".date("Y",$this->view->project_info['date_create'])." года";
           
            $html = $this->view->render('project/project-history.phtml');
            die (Zend_Json::encode(array('status' => 1,'html' => $html)));
        }
    }
    
    // редактирование проекта
    function projecteditAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            
            $project_id = (int)$this->getRequest()->getPost('project_id');
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            
            $user = new Application_Model_User();
            $project = new Application_Model_Projects();
            $members_status = new Application_Model_ProjectsMembers();
            
            $projects = $project->showcur($project_id);
            
            if(count($projects)>0)
            {    
                $users = $user->userinfo($projects['requestor_id']);
                $projects['requestor_name'] = $users->name;
                $projects['requestor_family'] = $users->family;
                $projects['requestor_lvl'] = $users->lvl;
                // учасники
                if($projects['members_id'] != "0")
                {
                    // список ролей учасников
                    $role_info = new Application_Model_ProjectsMembersRole();
                    $role_res = $role_info->showall();
                    if(count($role_res)>0)
                    {
                        $result[0] = "Выберите роль";
                        foreach($role_res as $k=>$v)
                        {
                            $result[$v['id']] = $v['title'];
                        }
                        $this->view->role = $result;
                    }
                    
                    $members = str_replace(";",',',$projects['members_id']);
                    $projects['members'] = $user->showmypeople($members);
                    foreach($projects['members'] as $k => $v)
                    {
                        $status = $members_status->showstatus($project_id,$v['id']);
                        $projects['members'][$k]['members_status'] = $status['status'];
                        // узнаем роли учасников
                        $cur_role = $members_status->membersinfolow($project_id,$v['id']);
                        $projects['members'][$k]['role'] = $cur_role['role_id'];
                    }
                }
                else $members = 0;
            }
            
            // список всех задач доступных создателю проекта
            // и список задач учасников
            $task = new Application_Model_Tasks();
            $status = '0,1,2,3,4,5,6';
            
            // информация по текущему пользователю
            $result_check_member = $members_status->membersinfolow($project_id, $cur_id);
            
            if($result_check_member['rights_id'] == '0')
                $tasks = $task->showall($members,$project_id,$status,$projects['requestor_id']);
            else
                $tasks = $task->showall($members,$project_id,$status);
           // echo("<pre>");
           // die(var_dump(array('m'=>$members,'p'=>$project_id,'s'=>$status)));
            $count_t = count($tasks);
            
            // список текущих выбраных задач
            if($projects['tasks_id'] != 0)
                $tasks_cur = explode(";", $projects['tasks_id']);
            else
                $tasks_cur = array();
            
            
            if(count($tasks_cur)>0)
            {
                $price = 0;
                foreach($tasks as $key => $val)
                {
                    if(in_array($val['id'], $tasks_cur))
                        $price+=$val['price'];
                }
            }
            
            
            
            if(count($tasks)>0)
            {
                /*for($i=0;$i<$count_t;$i++)
                {
                    $task_str .= $tasks[$i]['id'].',';
                }
                $task_str = mb_substr($task_str, 0,-1,'utf8');
                */
                $task_str = str_replace(';', ',', $projects['tasks_id']);
                
                // узнаем приватность задач
                $privacy_task = new Application_Model_ProjectsTasks();
                $private = $privacy_task->showtasks($task_str,$project_id);
                if(count($private)>0)
                {
                    foreach($private as $k=>$v)
                    {
                        $privatez[$v['task_id']] = $v;
                    }
                }
                
                foreach($tasks as $k=>$v)
                {
                    if(in_array($v['id'], $tasks_cur))
                        $tasks[$k]['privacy'] = $privatez[$v['id']]['status'];
                    
                }
               
            }
           // echo("<pre>");
           // die(var_dump($tasks));
            // список всех задач
            $this->view->tasks = $tasks;
            $this->view->price = $price;
            $this->view->tasks_cur = $tasks_cur;
            $this->view->projects = $projects;
            $this->view->terms = round($projects['term']/60/60/24/30);
            
            $html = $this->view->render('project/project-edit.phtml');
            
            die(Zend_Json::encode(array('status' => 1,'html' => $html)));
        }
     
    }
    
    // сохраняем отредактированый проект
    function saveprojectAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            
            // проверка принадлежности проекта пользователю
            $cur_id = Zend_Auth::getInstance()->getIdentity()->id;
            $project_id = $this->getRequest()->getPost('project-id');
            $project = new Application_Model_Projects();
            $projectmembers = new Application_Model_ProjectsMembers();
            $tasks_privacy = new Application_Model_ProjectsTasks();
            $projects = $project->showcur($project_id);
            // информация по текущему пользователю
            $result_check_member = $projectmembers->membersinfolow($project_id, $cur_id);
            $this->view->member_info = $result_check_member;
            
            if(($projects['requestor_id'] == $cur_id || $result_check_member['rights_id'] == '0') && $projects['flag'] == 1)
            {    
                    // старые данные 
                    $project_data['project-old-name'] = htmlspecialchars($this->getRequest()->getPost('project-old-name'));
                    $project_data['project-old-goal'] = htmlspecialchars($this->getRequest()->getPost('project-old-goal'));
                    $project_data['project-old-budget'] = $this->getRequest()->getPost('project-old-budget');
                    $project_data['project-old-budget-value'] = $this->getRequest()->getPost('project-old-budget-value');
                    $project_data['project-old-statute'] = htmlspecialchars($this->getRequest()->getPost('project-old-statute'));
                    $project_data['project-old-content'] = htmlspecialchars($this->getRequest()->getPost('project-old-content'));
                    $project_data['project-old-term-value'] = $this->getRequest()->getPost('project-old-term-value');
                    $project_data['project-old-tasks'] = $this->getRequest()->getPost('project-old-tasks');
                    $project_data['project-old-members'] = $this->getRequest()->getPost('project-old-members');
                    $project_data['project-old-private'] = (int)$this->getRequest()->getPost('project-old-private');
                    // текущие данные
                    $project_data['project-new-name'] = htmlspecialchars($this->getRequest()->getPost('project-name'));
                    $project_data['project-new-goal'] = htmlspecialchars($this->getRequest()->getPost('project-goal'));
                    $project_data['project-new-budget'] = $this->getRequest()->getPost('project-budget');
                    $project_data['project-new-budget-value'] = $this->getRequest()->getPost('budget-value');
                    if($project_data['project-new-budget-value'] == 1)
                            $value = "руб.";
                    if($project_data['project-new-budget-value'] == 2)
                            $value = "USD";
                    $project_data['project-new-statute'] = htmlspecialchars($this->getRequest()->getPost('project-statute'));
                    $project_data['project-new-content'] = htmlspecialchars($this->getRequest()->getPost('project-content'));
                    $project_data['project-new-term-value'] = $this->getRequest()->getPost('term-value');
                    $project_data['project-new-privacy'] = (int)$this->getRequest()->getPost('privacy');
                    
                   
                    // текущая информация по задачам 
                    $project_tasks = $this->getRequest()->getPost('task-related-mas');
                    $tasks_private = $this->getRequest()->getPost('privacy-task');
                    
                    // старые данные по задачам проекта
                    $project_old_tasks_str = $projects['tasks_id'];
                    
                    if(count($project_tasks)>1)
                        $tasks_id = implode(";", $project_tasks);
                    elseif(count($project_tasks) == 1)
                        $tasks_id = $project_tasks[0];
                    else
                        $tasks_id = 0;
                    // записываем приватность задач проекта
                    // Определяем массив задач для удаления
                    $project_old_tasks = explode(';', $project_old_tasks_str); // массив задач для удаления
                    
                    if(count($project_old_tasks)>0)
                    {
                        if(!(count($project_tasks)>0))
                           $project_tasks = array();
                        
                        foreach($project_old_tasks as $k => $v)
                        {
                            if(!in_array($v,$project_tasks))
                                $project_delete_mas[] = $v;    
                                
                        }
                        
                        // удаляем старые задачи
                        if(count($project_delete_mas)>0)
                        {    
                            $project_old_tasks_str = implode(',',$project_delete_mas);
                            $tasks_privacy->delete($project_old_tasks_str,$project_id);
                        }   
                    }
                    
                    // проверяем есть ли новые задачи, и/или апдейтим старые задачи
                    if(count($project_tasks)>0)
                    {   
                        // Сделать функционал добавления новых, апдейта существующих, удаления старых задач из таблички.
                        // массив - текущих задач
                        if(count($project_tasks)>0)
                        {
                            $tasks_private_update = array();
                            $tasks_private_new = array();
                            
                            foreach($project_tasks as $k => $v)
                            {
                                if(in_array($v,$project_old_tasks))
                                {
                                    $project_tasks_update[] = $v;
                                    $tasks_private_update[] = $tasks_private[$k];
                                }
                                else
                                {
                                    $project_tasks_new[] = $v;
                                    $tasks_private_new[] = $tasks_private[$k];
                                }    
                            }
                        }
                        
                        
                        // добавляем новые если есть
                        if(count($project_tasks_new)>0)
                        {
                            foreach($project_tasks_new as $k => $v)
                            {
                                $data = array(
                                   'project_id' => $project_id,
                                   'task_id'    => $v,
                                   'status'     => $tasks_private_new[$k]
                                );
                                $tasks_privacy->insert($data);
                                $data = array();
                            }
                        }
                        
                        // update старых задач
                        if(count($project_tasks_update)>0)
                        {
                            foreach($project_tasks_update as $k => $v)
                            {
                                $data = array(
                                   'project_id' => $project_id,
                                   'task_id'    => $v,
                                   'status'     => $tasks_private_update[$k]
                                );
                                $tasks_privacy->update($data);
                                $data = array();
                            }
                        }
                        
                    }
                    
                    
                    // текущая информация по учасникам 
                    $members_role = $this->getRequest()->getPost('members-role');
                    $members = $this->getRequest()->getPost('members');
                    if(count($members)>0)
                        $members_id = implode(";", $members);
                    else
                        $members_id = 0;
                    $data_notification['project_old_name'] = $project_data['project-old-name'];
                    $data_notification['project_name'] = $project_data['project-new-name'];
                    $data_notification['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
                    $data_notification['project_id'] = $project_id;
                    
                    // если менялось название проекта
                    if($project_data['project-old-name'] != $project_data['project-new-name'])
                    {
                        $data = array(
                          'name' => htmlspecialchars($project_data['project-new-name']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Название проекта',
                            'old_value' => $project_data['project-old-name'],
                            'new_value' => $project_data['project-new-name'],
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['project_name'] = $project_data['project-new-name'];
                    }   
                    // если менялось приватность проекта
                    if($project_data['project-old-private'] != $project_data['project-new-privacy'])
                    {
                        $data = array(
                          'status' =>  (int)($project_data['project-new-privacy']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Приватность проекта',
                            'old_value' => $project_data['project-old-private'],
                            'new_value' => $project_data['project-new-privacy'],
                            'user_id' => $cur_id
                        );
                       $project_log->insert($data);
                       switch ($project_data['project-new-privacy'])
                       {
                           case 1:
                               $privacy = 'Для всех';
                               break;
                           case 4:
                               $privacy = 'Для учасников';
                               break;
                           case 7:
                               $privacy = 'Для автора';
                               break;
                       }
                       
                       
                       //массив для оповещения на email
                       $data_notification['sub_params']['project_privacy'] = $privacy;
                    }   
                    
                    
                    // если менялось цель проекта
                    if($project_data['project-old-goal'] != $project_data['project-new-goal'])
                    {
                        $data = array(
                          'goal' =>  htmlspecialchars($project_data['project-new-goal']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Цель проекта',
                            'old_value' => $project_data['project-old-goal'],
                            'new_value' => $project_data['project-new-goal'],
                            'user_id' => $cur_id
                        );
                       $project_log->insert($data);
                       
                       //массив для оповещения на email
                       $data_notification['sub_params']['project_goal'] = $project_data['project-new-goal'];
                    }   
                    // если менялось бюджет проекта
                    if($project_data['project-old-budget'] != $project_data['project-new-budget'])
                    {
                        $data = array(
                          'budget' =>  htmlspecialchars($project_data['project-new-budget']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Бюджет проекта',
                            'old_value' => $project_data['project-old-budget'],
                            'new_value' => $project_data['project-new-budget'],
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['budget'] = $project_data['project-new-budget'];
                    } 
                     
                    // если менялось валюта бюджета проекта
                    if($project_data['project-old-budget-value'] != $value)
                    {
                        $data = array(
                          'budget_value' =>  $value 
                        );
                        $project->update($data, $project_id); 
                
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Валюта бюджета проекта',
                            'old_value' => $project_data['project-old-budget-value'],
                            'new_value' => $value,
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['budget_value'] = $value;
                    } 
                    // если менялось устав проекта
                    if($project_data['project-old-statute'] != $project_data['project-new-statute'])
                    {
                        $data = array(
                          'statute' =>  htmlspecialchars($project_data['project-new-statute']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Устав проекта',
                            'old_value' => $project_data['project-old-statute'],
                            'new_value' => $project_data['project-new-statute'],
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['project_statute'] = $project_data['project-new-statute'];
                    } 
                    // если менялось описание проекта
                    if($project_data['project-old-content'] != $project_data['project-new-content'])
                    {
                        $data = array(
                          'content' =>  htmlspecialchars($project_data['project-new-content']) 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Описание проекта',
                            'old_value' => $project_data['project-old-content'],
                            'new_value' => $project_data['project-new-content'],
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['project_content'] = $project_data['project-new-content'];
                    } 
                    // если менялось срок проекта
                    if($project_data['project-old-term-value'] != $project_data['project-new-term-value'])
                    {
                        $terms = $project_data['project-new-term-value']*30*24*60*60;
                        $data = array(
                          'term' =>  $terms 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Сроки проекта',
                            'old_value' => $project_data['project-old-term-value'],
                            'new_value' => $project_data['project-new-term-value'],
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        $data_notification['sub_params']['project_term'] = $project_data['project-new-term-value'];
                    } 
                    
                    $tasks = new Application_Model_Tasks();
                    $members_param = ($members_id == 0) ? 'project_no_member' : 'project_yes_member'; 
                    $tasks_param = ($tasks_id == 0) ? 'project_no_task' : 'project_yes_task';
                    
                    // если менялись задачи проекта
                    if($project_data['project-old-tasks'] != $tasks_id)
                    {
                        $data_notification['sub_params'][$tasks_param] = '';
                        
                        $data = array(
                          'tasks_id' =>  $tasks_id 
                        );
                        $project->update($data, $project_id); 
                        
                        // запись в лог проектов
                        $old_tasks = $project_data['project-old-tasks'];
                        $new_tasks = $tasks_id;
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Задачи проекта',
                            'old_value' => $old_tasks,
                            'new_value' => $new_tasks,
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        //массив для оповещения на email
                        if($tasks_id != 0)
                        {
                            $tasks_info = $tasks->showProjectTask(str_replace(';', ',', $tasks_id));
                            if(count($tasks_info) > 0)
                            {   
                                $end_task = array_pop($tasks_info);
                                foreach($tasks_info as $key => $val)
                                {
                                    $data_notification['sub_params'][$tasks_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/task-'.$val['id'].'/">'.$val['task_name'].'</a>, ';
                                }
                                $data_notification['sub_params'][$tasks_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/task-'.$end_task['id'].'/">'.$end_task['task_name'].'</a>';
                            }
                        }
                        
                        // корректировка измененых задач пользователя
                        // идем по старому масиву задач и обнуляем их если нет соответствия в  новом
                        if($project_data['project-old-tasks'] != 0 && $tasks_id != 0)
                        {
                             if(substr_count($project_data['project-old-tasks'], ';') > 0)
                                $old_mas = explode(";", $project_data['project-old-tasks']);
                             if(substr_count($project_data['project-old-tasks'], ';') == 0)
                                $old_mas[0] = $project_data['project-old-tasks'];
                             foreach($old_mas as $k => $v)
                             {
                                 if(!in_array($v, $project_tasks))
                                 {
                                     $tasks_update_str[] = $v;
                                 }
                             }
                        }
                        else $tasks_update_str = array();
                        
                        // устанавливаем project_id = 0 для отсутствующих задач
                        $tasks_obj = new Application_Model_Tasks(); 
                        if(count($tasks_update_str)>0)
                        {   
                            $tasks_str = implode(",", $tasks_update_str);
                            $tasks_obj->updatelist($tasks_str, '0');
                        }
                        else
                        {
                            $tasks_str = str_replace(";",",", $project_data['project-old-tasks']);
                            $tasks_obj->updatelist($tasks_str, '0');
                        }
                        // устанавливаем project_id = текущему проекту для задач
                        $tasks_id = str_replace(";", ",", $tasks_id);
                        $tasks_obj->updatelist($tasks_id,$project_id);
                   } 
                   //die($project_data['project-old-members'].'===='.$members_id);
                   
                   
                   // если менялись учасники проекта
                   if($project_data['project-old-members'] != $members_id)
                   {
                        $data_notification['sub_params'][$members_param] = '';
                        
                        $data = array(
                          'members_id' =>  $members_id 
                        );
                        $project->update($data, $project_id); 
                        // запись в лог проектов
                        $project_log = new Application_Model_ProjectsLog();
                        $data = array(
                            'project_id' => $project_id,
                            'date' => time(),
                            'field_name' => 'Учасники проекта',
                            'old_value' => $project_data['project-old-members'],
                            'new_value' => $members_id,
                            'user_id' => $cur_id
                        );
                        $project_log->insert($data);
                        
                        // работа с заявками учасников статус = 1
                        $request = new Application_Model_ProjectsRequests();
            //            $projectmembers = new Application_Model_ProjectsMembers();
                        
                        if($members_id == 0)
                        {
                            // работа с таблицей projects_requests
                            $data = array('status' => '3');
                            $requests = $request->updatelist($data, $project_id); 
                            // работа с таблицей projects_members
                            $projectmembers->deletelist($project_id);
                            
                            //собираем id удаленных участников проекта
                            if($project_data['project-old-members']!='')
                            {
                                $members_mas = explode(";", $project_data['project-old-members']);
                            }
                        }
                        else  
                        {   
                            $user = new Application_Model_User();
                            $members_info = $user->userinfoGroup(str_replace(';', ',', $members_id));
                            if(count($members_info) > 0)
                            {
                                $end_member = array_pop($members_info);
                                foreach($members_info as $key => $val)
                                {
                                    $data_notification['sub_params'][$members_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/id'.$val['id'].'/">'.$val['name'].' '.$val['family'].'</a>, ';
                                }
                                $data_notification['sub_params'][$members_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/id'.$end_member['id'].'/">'.$end_member['name'].' '.$end_member['family'].'</a>';
                            }
                            
                            // сравниваем то что было с новым
                            $old_mas = explode(";", $project_data['project-old-members']);
                            $new_mas = $members;
                            foreach($old_mas as $k => $v)
                            {    
                                if(!in_array($v, $new_mas))   
                                {
                                    // изменяем статус 3
                                    $data = array(
                                        'status' => '3'
                                    );
                                    $requests = $request->update($data,$project_id,$v);
                                    // удалить из project_members
                                    $projectmembers->delete($project_id, $v);
                                    $members_mas[] = $v; 
                                }
                                else
                                    $members_mas_stat[] = $v; 
                            }
                            // если удалялись существующие учасники
                            if(count($members_mas)>0)
                            {    
                                if(count($members_mas)>1)
                                    $members_str = implode(",", $members_mas);
                                elseif(count($members_mas) == 1)
                                    $members_str = $members_mas[0];
                                $member_tasks_id = $tasks->showall($members_str,$project_id);
                                foreach($member_tasks_id as $k=>$v)
                                {
                                    $id_mass[$k] = $v['id'];
                                }
                            }
                            foreach($new_mas as $k => $v)
                            {
                                if(!in_array($v, $old_mas))
                                {
                                    // проверить есть ли учасник в таблице заявок
                                    if($request->checkmember($v, $project_id))
                                    {    
                                        // обновляем запись в заявках пользователей
                                        $data = array('status' => 1);
                                        $requests = $request->update($data, $project_id, $v);
                                    }
                                    else
                                    {    
                                        // добавляем запись в заявки пользователей
                                        $data = array(
                                         'member_id' => $v,
                                         'project_id' => $project_id,
                                         'request_reg' => time(),
                                         'status' => '1'
                                        );
                                        $requests = $request->insert($data);
                                    }
                                    // добавляем запись в учасники проекта
                                    $data = array(
                                       'member_id' => $v,
                                       'project_id' => $project_id,
                                       'status' => '1',
                                       'rights_id' => '1' 
                                    );
                                    $projectmembers->insert($data);
                                    
                                    $new_member_mass[]= $v;
                                }
                            }
                        } 
                    }
                    else
                    {
                        if($project_data['project-old-members']!='')
                            $members_mas_stat = explode(";", $project_data['project-old-members']);
                    }
                    
                    // обновляем статусы учасников проекта
                    
                    if(count($members)>0)
                    {    
                        $projectmembers = new Application_Model_ProjectsMembers();
                        foreach($members as $k => $v)
                        {
                            // запись с учасником
                            $data = array(
                                'role_id' => $members_role[$k]
                            );
                            $projectmembers->updatemember($data,$project_id,$v);
                        }
                    }
                    
                    $user = new Application_Model_User();
                    if(count($data_notification)>1)
                    {
                        //Оповещение на почту
                        $notification = new Application_Model_Notification();
                        
                        //оповещение снятых с проекта пользователей
                        if(count($members_mas)>0)
                        {
                            //NODE
                            $id_list = implode(",", $members_mas);
                            // инфа по группе пользователей
                            $members_mas_info = $user->userinfoGroup($id_list);
                            sort($members_mas_info);
                            sort($members_mas);
                            
                            foreach($members_mas as $key => $val)
                            {
                                // on email
                                $notification->send('project_member_delete', $val, $data_notification);
                                //user_delete_from_project
                                // NODE
                                $user_arr[] = $val;
                                $lang = $members_mas_info[$key]['lang'];
                                $header_const[] = constant('PROJECT_'.strtoupper($lang));
                                $data_text[] = constant('PROJECT_CANSEL_'.strtoupper($lang));
                                // sub_type
                                $user_href[3][] = 'removed_members';
                                $user_href[4][] = 'project_member_delete';
                            }
                        }
                        
                        //оповещение добавленных в проект пользователей
                        if(count($new_member_mass)>0)
                        {
                            $project_log = new Application_Model_ProjectsLog();
                            //NODE
                            $id_list = implode(",", $new_member_mass);
                            // инфа по группе пользователей
                            $new_member_mass_info = $user->userinfoGroup($id_list);
                            sort($new_member_mass_info);
                            sort($new_member_mass);
                            
                            foreach($new_member_mass as $key => $val)
                            {
                                // on email
                                $notification->send('project_member_add_request', $val, $data_notification);
                                //user_add_to_project
                                // запись в лог проектов
                                $data = array(
                                    'project_id' => $project_id,
                                    'date' => time(),
                                    'field_name' => 'Участие в проекте',
                                    'old_value' => 'Заявлен',
                                    'new_value' => $val,
                                    'user_id' => $cur_id
                                );
                                $project_log->insert($data);
                                // NODE
                                $user_arr[] = $val;
                                $lang = $new_member_mass_info[$key]['lang'];
                                $header_const[] = constant('PROJECT_'.strtoupper($lang));
                                $data_text[] = constant('PROJECT_PARTITION_'.strtoupper($lang));
                                $user_href[3][] = 'new_members';
                                $user_href[4][] = 'project_member_add_request';
                            }
                        }
                        
                        //оповещение неизменных пользователей
                        if(count($members_mas_stat)>0)
                        {
                            $members_mas_stat = array_unique($members_mas_stat);
                            //NODE
                            $id_list = implode(",", $members_mas_stat);
                            // инфа по группе пользователей
                            $members_mas_stat_info = $user->userinfoGroup($id_list);
                            sort($members_mas_stat_info);
                            sort($members_mas_stat);
                            
                            foreach($members_mas_stat as $key => $val)
                            {
                                // on email
                                if($val != $cur_id)
                                    $notification->send('project_edit', $val, $data_notification);
                                // user_edit_project
                                // NODE
                                $user_arr[] = $val;
                                $lang = $members_mas_stat_info[$key]['lang'];
                                $header_const[] = constant('PROJECT_'.strtoupper($lang));
                                $data_text[] = constant('PROJECT_EDIT_'.strtoupper($lang));
                                $user_href[3][] = 'current_members';
                                $user_href[4][] = 'project_edit';
                            }
                        }
                        
                        // редактировал учасник
                        if($projects['requestor_id'] != $cur_id)
                        {
                            //оповещение создателя проекта
                            $notification->send('project_edit', $projects['requestor_id'], $data_notification);
                        }
                        else // редактировал создатель
                            $notification->send('project_edit', $cur_id, $data_notification);
                    }
                }
                
                ///////////////////////////////////////////
                // отсылаем на Ноду уведомление
                ////////////////////////////////////////////
                $requestor_info = $user->userinfo($cur_id,"low"); // заказчик
                
                $notificationNode = new NotificationNode();
                $type = "project";
                //определяем язык 
                //$lang = $requestor_info['lang']; // на языке получателя
                
                //$user_arr[] = $project['requestor_id'];
               
                //$header_const[] = constant('PROJECT_'.strtoupper($lang));
                $user_href[0] = $requestor_info['name']." ".$requestor_info['family']; // заказчик
                $user_href[1] = $project_data['project-new-name']; // имя проекта
                $user_href[2] = $cur_id; // заказчик
                $img_arr = $requestor_info['img_url']; // заказчик
                $universal_id = $project_id;
                
                // если проект редактрирует учасник то не отсылать ему уведомления
                if($projects['requestor_id'] != $cur_id)
                {
                    $remove_key = array_search($cur_id, $user_arr);
                
                    unset($user_arr[$remove_key]);
                    unset($header_const[$remove_key]);
                    unset($data_text[$remove_key]);
                    unset($user_href[3][$remove_key]);
                    unset($user_href[4][$remove_key]);
                    
                    
                    // добавить заказчика (редактирование проетка)
                    $requestor_info_req = $user->userinfo($projects['requestor_id'],"low"); // заказчик
                                                    
                    $user_arr[] = $projects['requestor_id'];
                    $header_const[] = constant('PROJECT_'.strtoupper($requestor_info_req['lang']));
                    $data_text[]    = constant('PROJECT_EDIT_'.strtoupper($requestor_info_req['lang']));
                    $user_href[3][] = 'current_members';
                    $user_href[4][] = 'project_edit';
                    
                    sort($user_arr);
                    sort($header_const);
                    sort($data_text);
                    sort($user_href[3]);
                    sort($user_href[4]);
                    
                    
                }
                // нотификация через node сервер
               /* $stat_ar = array(
                    'users_mas'     => $user_arr,
                    'type'          => $type,
                    'header_const'  => $header_const,
                    'img_arr'       => $img_arr,
                    'data_text'     => $data_text,
                    'user_href'     => $user_href,
                    'universal_id' => $universal_id
                );*/
                //die(var_dump($stat_ar));
                $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$data_text,$user_href,$universal_id);
                
                
                die (Zend_Json::encode(array('status' => 1)));    
        }
    }
    
    // выводим все проекты
    function projectsAction()
    {
        $project_members = new Application_Model_ProjectsMembers();
        $project = new Application_Model_Projects();
        $projects = $project->showall(); // вывод всех публичных проектов + своих проектов + где я учасник
        $user = new Application_Model_User();
        
        if(count($projects[0])>0)
        {    
            // все проекты со статусами 1 и 4
            if(count($projects[0])> 0)
            {
                foreach($projects[0] as $k => $v)
                {
                    // формируем масив проектов со статусами 1 и 4
                    if($v['status'] == 1)
                        $projects_all[] = $v; // все проекты со статусом 1
                    else
                    {
                        $projects_mem[] = $v; // все проекты со статусом 4
                        $id_mass_member[] = $v['id']; // ид проектов
                    }
                }
                
                $projects_corect = array();
                
                // если есть проекты со статусом 4
                if(count($id_mass_member)>0)
                {
                    $project_str = implode(",",$id_mass_member);
              
                    // узнаем ид проектов в которых я учавствую
                    $result_id = $project_members->inprojectsid($project_str);
                    if(count($result_id)>0)
                    {
                        foreach($result_id as $k=>$v)
                        {
                            $result_id_c[$k] = $v['project_id'];
                        }
                    
                        // откидываем проекты в которых я участвую
                        foreach($projects_mem as $k => $v)
                        {
                            // собераем массив проектов в которых я учасник
                            if(in_array($v['id'], $result_id_c))
                                $projects_corect[] = $v;
                        }
                    }
                }
                
               // die(var_dump($x));
                // обьединяем массивы со статусом 1(для всех) и 4(те где я учасник)
                if(is_null($projects_all))
                    $projects_all = array();
                if(is_null($projects_corect))
                    $projects_corect = array();
                
                $projects_res_mass = array_merge($projects_all,$projects_corect);
                
            }
            
            //if(count($projects[1]) 0)
                
            
            
            $projects_result = array_merge($projects_res_mass,$projects[1]);
            
            $projects_result = array_reverse($projects_result);
            
            foreach($projects_result as $k => $v)
            {
                // заказчик
                $users = $user->userinfo($v['requestor_id']);
                $projects_result[$k]['requestor_name'] = $users->name;
                $projects_result[$k]['requestor_family'] = $users->family;
                $projects_result[$k]['requestor_lvl'] = $users->lvl;
                $projects_result[$k]['img_url'] = $users->img_url;
                
                // учасники
                if ($v['members_id'] != "")
                {
                    $projects_result[$k]['count_members'] = substr_count($v['members_id'], ';');
                    
                    $members = str_replace(";",',',$v['members_id']);
                    $projects_result[$k]['members'] = $user->showmypeople($members);
                    
                }
            }
        }
        
        $this->view->projects = $projects_result;
      //  if(!isset($_GET['act']))
            $this->render('projects');
      //  else
      //      $this->render('projects2');
    }
    
    function pageAction()
    {
        $this->render('projectpage');
    }
    
    function concursAction()
    {
        $this->render('concurs');
    }
    
    function fondAction()
    {
        
        
        $this->render('fond');
    }
    
    function bisnesAction()
    {
        $this->render('bisnes');
    }
    
    // создание нового проекта
    function newAction()
    {
        if(!Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('auth/login');
        
        
        $user_cat = new Application_Model_UsersCategoryList();
        
        $users_list = $user_cat->showfriendswithInfo();
        $this->view->users_list = $users_list;
        $task = new Application_Model_Tasks();
        $status = "1,5,6";
        $tasks_list = $task->showNewProjectTask($status);
        
        if(count($tasks_list)==0)
            $tasks_list = array();
            
        $this->view->tasks = $tasks_list;
        $this->render('new');
    }
    
    // показываем связаные задачи
    function addtasksAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            $task = new Application_Model_Tasks();
            $tasks = $task->showall();
            if (count($tasks)>0)
            {
                foreach($tasks as $k => $v)
                {
                    $tasks_sel[$tasks[$k]['id']] = $v['task_name'];
                    
                }
            }
             
            $this->view->tasks = $tasks_sel;
            $html = $this->view->render('project/related-tasks.phtml');
            
            die (Zend_Json::encode(array('status' => 1,'html' => $html)));
        }
    }
    
    
    // создаем новый проект
    function doprojectAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            
            // получаем параметры обьекта
            $project_data['name'] = htmlspecialchars($this->getRequest()->getPost('project-name'));
            $project_data['project-goal'] = htmlspecialchars($this->getRequest()->getPost('project-goal'));
            $project_data['project-budget'] = (int)$this->getRequest()->getPost('project-budget');
            $project_data['budget-value'] = (int)$this->getRequest()->getPost('budget-value');
            $project_data['project-statute'] = htmlspecialchars($this->getRequest()->getPost('project-statute'));
            $project_data['project-content'] = htmlspecialchars($this->getRequest()->getPost('project-content'));
            $project_data['term-value'] = (int)$this->getRequest()->getPost('term-value');
            $project_data['privacy'] = (int)$this->getRequest()->getPost('privacy');
            
            
            
//            $project_data['status'] = (int)$this->getRequest()->getPost('status');
            
            // роли учасников
            $members_role = $this->getRequest()->getPost('members-role');
            // права учасников
            $members_right = $this->getRequest()->getPost('members-rights');
            // учасники
            $members = $this->getRequest()->getPost('members');
            $memb = 0; // new
            
            
            
            if(count($members)>0)
            {    
                $members_str = implode(";", $members);
                $memb = 1;
                // делаем запись 
            }
            else
                $members_str = 0;
            
            // информация по задачам
            $tasks = $this->getRequest()->getPost('task-related-mas');
            $tasks_private = $this->getRequest()->getPost('privacy-task');
            
            if(count($tasks)>0)
                $tasks_id = implode(";", $tasks);
            else
                $tasks_id = 0;
           
            if($project_data['budget-value'] == 1)
                $value = "руб.";
            if($project_data['budget-value'] == 2)
                $value = "USD";
            
            $term = 60*60*24*30*$project_data['term-value'];
            
            $curid = Zend_Auth::getInstance()->getIdentity()->id;
            $projects = new Application_Model_Projects();
            
            //if($project_data['status'] != 4)
//                $project_data['status'] = 1;
            
            $data = array(
                'tasks_id' => $tasks_id,
                'status' => $project_data['privacy'],
                'members_id' => $members_str,
                'name' => $project_data['name'],
                'content' => $project_data['project-content'],
                'goal' => $project_data['project-goal'],
                'budget' => $project_data['project-budget'],
                'budget_value' => $value,
                'statute' => $project_data['project-statute'],
                'term' => $term,
                'requestor_id' => $curid,
                'date_create' => time()
            );
            $projects->insert($data);
            $last_id = $projects->last_id(); 
            
            // записываем приватность задач проекта
            if(count($tasks)>0)
            {    
                $tasks_privacy = new Application_Model_ProjectsTasks();
                foreach($tasks as $k => $v)
                {
                    $data = array(
                       'project_id' => $last_id,
                       'task_id'    => $v,
                       'status'     => $tasks_private[$k]
                    );
                    $tasks_privacy->insert($data);
                    $data = array();
                }
            }
            
            $members_param = ($data['members_id'] == 0) ? 'project_no_member' : 'project_yes_member'; 
            $tasks_param = ($data['tasks_id'] == 0) ? 'project_no_task' : 'project_yes_task'; 
            $data['sub_params'][$members_param] = '';
            $data['sub_params'][$tasks_param] = '';
            
            //запись в лог проектов
            if ($memb == 1)
            {
                $project_log = new Application_Model_ProjectsLog();
                foreach($members as $val)
                {
                   // запись в лог проектов
                   $data_log = array(
                        'project_id' => $last_id,
                        'date' => time(),
                        'field_name' => 'Участие в проекте',
                        'old_value' => 'Заявлен',
                        'new_value' => $val,
                        'user_id' => $curid
                    );
                    $project_log->insert($data_log);
                }
            }
            
            //нформация участников проекта
            if($members_str != 0)
            {
                $user = new Application_Model_User();
                $members_info = $user->userinfoGroup(str_replace(';', ',', $members_str));
                $members_info_const = $members_info;
                if(count($members_info) > 0)
                {
                    $end_member = array_pop($members_info);
                    foreach($members_info as $key => $val)
                    {
                        $data['sub_params'][$members_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/id'.$val['id'].'/">'.$val['name'].' '.$val['family'].'</a>, ';
                    }
                    $data['sub_params'][$members_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/id'.$end_member['id'].'/">'.$end_member['name'].' '.$end_member['family'].'</a>';
                }
            }
            
            //нформация связаных задач
            if($tasks_id != 0)
            {
                $tasks = new Application_Model_Tasks();
                $tasks_info = $tasks->showProjectTask(str_replace(';', ',', $tasks_id));
                if(count($tasks_info) > 0)
                {   
                    $end_task = array_pop($tasks_info);
                    foreach($tasks_info as $key => $val)
                    {
                        $data['sub_params'][$tasks_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/task-'.$val['id'].'/">'.$val['task_name'].'</a>, ';
                    }
                    $data['sub_params'][$tasks_param].= '<a href="http://'.$_SERVER['HTTP_HOST'].'/task-'.$end_task['id'].'/">'.$end_task['task_name'].'</a>';
                }
            }
            
            $data['project_id'] = $last_id;
            $data['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
            $data['term_str'] = round($term/60/60/24/30);
            $data['project_name'] = $project_data['name'];
            
            //die(var_dump($data));
            
            //Оповещение на почту
            $notification = new Application_Model_Notification();
            $notification->send('project_create', $curid, $data);
            //author_create_project
            
            if(count($members)>0)
            {
                foreach($members as $key => $val)
                {
                    $notification->send('project_member_add_request', $val, $data);
                    //user_add_to_project
                }
            }
            
            // записать в таблицу tasks инфу о привязаных задачах
            if($tasks_id != 0)
            {
                $tasks = new Application_Model_Tasks();
                $tasks_id = str_replace(";", ",", $tasks_id);
                $tasks->updatelist($tasks_id, $last_id);
            }
            
            // запись в проджект мемберс
            if($memb == 1)
            {
                $projects_members = new Application_Model_ProjectsMembers();
                $projects_requests = new Application_Model_ProjectsRequests();
                sort($members_info_const);
                sort($members);
                foreach($members as $k => $v)
                {
                    // запись с учасником
                    $data = array(
                        'member_id'  => $v,
                        'project_id' => $last_id,
                        'status'     => 1,     // ожидает подтверждения
                        'role_id'    => $members_role[$k],
                        'rights_id'  => $members_right[$k],
                        'date'       => time()
                    );
                    $projects_members->insert($data);
                    // запись с заявкой учасника
                    $data = array(
                        'member_id'   => $v,
                        'project_id'  => $last_id,
                        'status'      => 1,     // заявлен на проект
                        'request_reg' => time()
                    );
                    $projects_requests->insert($data);
                    //NODE notification
                    
                    $lang = $members_info_const[$k]['lang']; // на языке получателя
                    
                    $header_const[$k] = constant('PROJECT_'.strtoupper($lang));
                    $user_arr[$k] = $v; // members
                    $data_text[$k] = constant('PROJECT_PARTITION_'.strtoupper($lang)); 
                    
                    $user_href[3][] = 'new_members';
                    $user_href[4][] = 'project_member_add_request';
                }
                
                ////////////////////////////////////////////////////////////////////
                // отсылаем на Ноду уведомление
                //////////////////////////////////////////////////
                $users = new Application_Model_User();
                $requestor_info = $users->userinfo($curid,"low");
                $notificationNode = new NotificationNode();
                $type = "project";
                //определяем язык 
                $user_href[0] = $requestor_info['name']." ".$requestor_info['family']; // заказчик
                $user_href[1] = $project_data['name']; // имя проекта
                $user_href[2] = $curid; // заказчик
                $img_arr = $requestor_info['img_url']; // заказчик
                $universal_id = $last_id;
                // нотификация через node сервер
                $notificationNode->sendToNode($user_arr,$type,$header_const,$img_arr,$data_text,$user_href,$universal_id);
                
            }
            die (Zend_Json::encode(array('status' => 1)));
        } 
    }
}

var querystring = require('querystring');
var http = require('http');
var server = require('http').createServer(handler)
  , io = require('socket.io').listen(server)
  
 
server.listen(1337);
console.log('start server');
function handler(req, res) {
    // set up some routes
    switch(req.url) {
        case '/push':
        if (req.method == 'POST') {
            console.log("[200] " + req.method + " to " + req.url);
            var fullBody = '';
            req.on('data', function(chunk) {
                fullBody += chunk.toString();
                if (fullBody.length > 1e6) {
                    // FLOOD ATTACK OR FAULTY CLIENT, NUKE REQUEST
                    req.connection.destroy();
                }
            });
            req.on('end', function() {              
                // Send the notification!
                var json = qs.stringify(fullBody);
                console.log(json.message);
                io.sockets.emit('push', { message: json.message });
                // empty 200 OK response for now
                res.writeHead(200, "OK", {'Content-Type': 'text/html'});
                res.end();
            });    
        }
        break;
        default:
        // Null
  };
}
var mas = new Array();
io.sockets.on('connection', function (socket) {
  
  var user_agent = socket.manager.handshaken[socket.id].query.user_agent;
  //console.log(user_agent);
  //console.log("connected... " + socket.manager.handshaken[socket.id].query.userid); 
  //console.log(socket);
  var id =  socket.manager.handshaken[socket.id].query.userid;
  var url = socket.manager.handshaken[socket.id].query.url;
  var cokie = socket.manager.handshaken[socket.id].query.php;
  
      //console.log('========>>>>>>>>>>>>>>>>>>>>>>'+cokie);
      
      if (typeof id === 'undefined')
console.log(1);
      else
      {
onlinestatus(id,1); // онлайн статус пользователей
// jпределить по куки что делать апдейт или инсерт
logusers(id,user_agent,url,cokie) // log_users
      }
  
  
  socket.on('message', function (data) {
var data = JSON.parse(data);
//console.log('=========='+data+'==============');
//console.log("query... " + socket.handshaken[socket.id].query.userid); 
switch(data.type)
{ 
 case 'message':
 //console.log('=========='+data+'==============');  
 if (data.userid.length >1)
 {
     for(i=0; i<data.userid.length; i++)
     {
socket.broadcast.emit('id'+data.userid[i],{type: data.type, dialog_number:data.dialog_number[i], id: data.userid, imgurl: data.imgurl, header: data.header[i], text: data.msj, dialog_id: data.dialog_id, users_href: data.users_href,users_ids:data.users_ids,flag_dialog:data.flag_dialog, notification_flag: data.notification_flag[i]});
     }
 }
 else
     socket.broadcast.emit('id'+data.userid,{type: data.type, dialog_number:data.dialog_number[0],id: data.userid, imgurl: data.imgurl, header: data.header, text: data.msj, dialog_id: data.dialog_id, users_href: data.users_href,users_ids:data.users_ids,flag_dialog:data.flag_dialog, notification_flag: data.notification_flag[0]});
 break;
 
 case 'message_update':
   
 if(data.userid.length >1)
 {
   
   for(i=0; i<data.userid.length; i++)
     {
socket.broadcast.emit('id'+data.userid[i],{type: data.type});
     }
 }
 else
   socket.broadcast.emit('id'+data.userid,{type: data.type});
 break;
 
 case 'dialog_update':
   
 if(data.userid.length >1)
 {
   for(i=0; i<data.userid.length; i++)
     {
socket.broadcast.emit('id'+data.userid[i],{type: data.type});
     }
 }
 
 break;
 
 case 'project_task_chat':
 console.log(data);
 for(i = 0; i < data.users_id.length; i++)
 {
     socket.broadcast.emit('id'+data.users_id[i],
 {
     type          : data.type,
     sender_id     : data.sender_id,
     text          : data.text ,
     sender_fio    : data.sender_fio,
     sender_url    : data.sender_url,
     subject_id    : data.subject_id,
     sender_post   : data.sender_post,
     up_time       : data.up_time
 });
 }
 break;
 
 case 'task':
   
   for(i=0; i<data.userid.length; i++)
     {
socket.broadcast.emit('id'+data.userid[i],{type: data.type, id: data.userid[i], imgurl: data.imgurl, header: data.header[i], text: data.msj[i], task_id: data.task_id, users_href: data.users_href, task_name: data.task_name, requestor_id:data.requestor_id ,notification_flag: data.notification_flag[i] ,notification_block: data.notification_block});
     }
 break;
 
 case 'project':
   
   for(i=0; i<data.userid.length; i++)
     {
//console.log(data.userid[i]+'======');
socket.broadcast.emit('id'+data.userid[i],{type: data.type, id: data.userid[i], imgurl: data.imgurl, header: data.header[i], text: data.msj[i], project_id: data.project_id, users_href: data.users_href, project_name: data.project_name, requestor_id:data.requestor_id, sub_type: data.sub_type[i] , notification_flag: data.notification_flag[i] ,notification_block: data.notification_block});
     }
 break;
 
 case 'users':
     
      for(i=0; i<data.userid.length; i++)
      {
        socket.broadcast.emit('id'+data.userid[i],{type: data.type, id: data.userid[i], imgurl: data.imgurl, header: data.header[i], text: data.msj[i], sender_id: data.sender_id, users_href: data.users_href, department_name: data.department_name, notification_flag: data.notification_flag[i] ,notification_block: data.notification_block});
      }
 break;
 case 'users_request':
      
      for(i=0; i<data.userid.length; i++)
      {
        socket.broadcast.emit('id'+data.userid[i],{type: data.type, id: data.userid[i], imgurl: data.imgurl, header: data.header[i], text: data.msj[i], sender_id: data.sender_id, users_href: data.users_href, department_name: data.department_name, notification_flag: data.notification_flag ,notification_block: data.notification_block});
      }
 break;
}	
  
  });
  socket.on('disconnect', function(){
      var id =  socket.manager.handshaken[socket.id].query.userid;
      var user_agent = socket.manager.handshaken[socket.id].query.user_agent;
      var url = socket.manager.handshaken[socket.id].query.url;
      var cokie = socket.manager.handshaken[socket.id].query.php;
      
      //console.log(typeof(id));
      
      if (typeof id === 'undefined')
console.log(1);
      else
      {
onlinestatus(id,0); // онлайн статус пользователей
logusers(id,user_agent,url,cokie) // log_users
      }
      
      
  });
});
function logusers(userid,useragent,url,cokie)
{
  var data = querystring.stringify({
      id: userid,
      page_url:url, 
      node_cokie:cokie
      
    });
 //
  // prepare the header
  var postheaders = {
      'User-Agent': useragent,
      'Content-Type' : 'application/x-www-form-urlencoded',
      'Content-Length' : Buffer.byteLength(data)
  };
  
  // the post options
  var optionspost = {
      host : 'do.arbitas.com',
      port : 80,
      path : '/profile/logusers/',
      method : 'POST',
      headers : postheaders
  };
  
  //console.info('Options prepared:');
  //console.info(optionspost);
  //console.info('Do the POST call');
  
  // do the POST call
  var reqPost = http.request(optionspost, function(res) {
      //console.log("statusCode: ", res.statusCode);
      // uncomment it for header details
      // console.log("headers: ", res.headers);
  
      res.on('data', function(d) {
 //console.info('POST result:\n');
 process.stdout.write(d);
 //console.info('\n\nPOST completed');
      });
  });
  
  // write the json data
  reqPost.write(data);
  reqPost.end();
  reqPost.on('error', function(e) {
      console.error(e);
});
}
function onlinestatus(userid,flag)
{
  var data = querystring.stringify({
      id: userid,
      flag_set: flag
    });
 //
  // prepare the header
  var postheaders = {
      'Content-Type' : 'application/x-www-form-urlencoded',
      'Content-Length' : Buffer.byteLength(data)
  };
  
  // the post options
  var optionspost = {
      host : 'do.arbitas.com',
      port : 80,
      path : '/profile/onlinestatusset/',
      method : 'POST',
      headers : postheaders
  };
  
  // do the POST call
  var reqPost = http.request(optionspost, function(res) {
      //console.log("statusCode: ", res.statusCode );
  
      res.on('data', function(d) {
 process.stdout.write(d);
      });
  });
  
  // write the json data
  reqPost.write(data);
  reqPost.end();
  reqPost.on('error', function(e) {
      console.error(e);
});
}


<?php
class AuthController extends Zend_Controller_Action {
    
    public function init() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            //если AJAX - отключаем авторендеринг шаблонов
            Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
        }
        
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('auth');
    }
    
    public function indexAction() {
        $this->_redirect('auth/login');
    }
    
    public function loginAction() {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('', 'profile');
        }    
        if ($this->getRequest()->isXmlHttpRequest()) { 
            $username = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');
            $password = md5($password);
            $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
            $authAdapter->setTableName('users')
                ->setIdentityColumn('email')
                ->setCredentialColumn('pass');
            $authAdapter->setIdentity($username)
                ->setCredential($password);
            $auth = Zend_Auth::getInstance(); 
            if($username == '' || $password == ''){
                $message ='Вы ввели неверное имя пользователя или неверный пароль';
                $status = 0;
                
            }else if($auth->authenticate($authAdapter)->isValid())  {
                $identity = $authAdapter->getResultRowObject();
                $authStorage = $auth->getStorage();
                $authStorage->write($identity);
                $status = 1;
            }else{
               // $message ='Вы ввели неверное имя пользователя или неверный пароль';
                $status = 2;
               // $this->_redirect('auth/forgot');
            }
            die (Zend_Json::encode(array('status' => $status, 'message' => $message)));
        }
    }
    
    public function forgotAction()
    {
        //$this->render('forgot'); 
    }
    
    public function logoutAction(){
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }
    
 
    public function homeAction() {
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if (!$data) {
            $this->_redirect('auth/login');
        }
        $this->view->username = $data->username;
    }
    
    public function confirmationAction() {
        $user = new Application_Model_User;
        $user_language = new Application_Model_UsersLanguage(); 
        $user_spec = new Application_Model_UsersSpecialization(); 
        
        $library = new Library;
        if ($this->getRequest()->isPost()) {
            if(isset($_POST['email'])) {
                
                if($user->issetEmail($_POST['email']))
                    die (Zend_Json::encode(array('status' => 0, 'message' => 'Пользователь с таким email уже существует')));
                $password = $library->genPassword(8);
                //die (Zend_Json::encode(array('status' => 0, 'message' => 'test')));
                if($library->sendEmail($_POST['email'],$password)) {
                    $data = array(
                        'email' => $_POST['email'],
                        'pass' => md5($password)
                    );
                    $user->addUser($data);
                    $user_id = $user->last_id(); 
                    $career = new Application_Model_Career();
                    $data_c = array(
                        'user_id' => $user_id 
                    );
                    $career->insert($data_c);
                    $null = '';
                    $user_language->insert($user_id, $null);
                    $user_spec->insert($user_id, $null);
                    
                    die (Zend_Json::encode(array('status' => 1,'message' => 'На Ваш емайл выслан пароль для авторизации на сайте.')));
                }
                else
                    die (Zend_Json::encode(array('status' => 0)));
            }
            elseif (isset($_POST['phone'])) {
                if($library->issetEmail($_POST['email']))
                    die (Zend_Json::encode(array('status' => 0, 'message' => 'Пользователь с таким телефоном уже существует')));
                die('sendphone');
            }
        }
    }
   public function rememberAction() {
        //$form = new RegistrationForm();
        //$this->view->form = $form;
   }
   
   public function checkrememberAction() {
        $library = new Library;
        $user = new Application_Model_User;
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $email = $this->getRequest()->getPost('email');
            if($email=='')
                die (Zend_Json::encode(array('status' => 0, 'message' => 'Введите email')));
            if(preg_match('/.+@.+\..+/i', $email)){
                $key = $library->sendRememberEmail($email);
                $user->changeKey(array('email'=>$email,'remember_key'=>$key));
                die (Zend_Json::encode(array('status' => 1, 'message' => 'ok')));
            }
            else
                die (Zend_Json::encode(array('status' => 0, 'message' => 'Введите корректный email')));
        }
   }
   
   public function changepassAction() {
       $user = new Application_Model_User;
       if ($this->getRequest()->isXmlHttpRequest()) {
            $password = $this->getRequest()->getPost('password');
            $password2 = $this->getRequest()->getPost('password2');
            $code = $this->getRequest()->getPost('code');
            if($password=='' or $password2=='') {
                die (Zend_Json::encode(array('status' => 0, 'message' => 'Поле пароль должно быть заполнено')));
            }
            elseif($password==$password2) {
                if($user->changepass($code,$password)) {
                    $user->erasecode($code);
                    die (Zend_Json::encode(array('status' => 1, 'message' => 'ok')));
                }
            } else {
                die (Zend_Json::encode(array('status' => 0, 'message' => 'Пароли не совпадают')));
            }
       } else {
           if(isset($_GET['code']) and isset($_GET['email'])) {
                $this->view->code = $_GET['code'];
            }
       }
   }
      public function signupAction() {
        
   }
   
}
<?php
class ProfileController extends Zend_Controller_Action
{
    public function init() {
        if(Zend_Auth::getInstance()->hasIdentity() == false) {
            $this->_helper->redirector('', '');
        }
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
        }
    }
 
    function indexAction() 
    {
        $users = new Application_Model_User();   
        $user = $users->userinfo(Zend_Auth::getInstance()->getIdentity()->id);
        $curid = Zend_Auth::getInstance()->getIdentity()->id;
        if (isset($_GET['id']) AND is_numeric($_GET['id']))
        {
            $user = $users->userinfo($_GET['id']);
            $curid = $_GET['id'];
        }
        $this->view->curid = $curid;    
        $regions = new Application_Model_Regions();
        $this->view->user = $user;
        $country = $regions->nameCountry($user->country);
        $region = $regions->nameRegion($user->region);
        $city = $regions->nameCity($user->city);
        $hcountry = $regions->nameCountry($user->hcountry);
        $hregion = $regions->nameRegion($user->hregion);
        $hcity = $regions->nameCity($user->hcity);
        $array = array(
            'country'=>$country['name'],
            'region'=>$region['name'],
            'city'=>$city['name'],
            'hcountry'=>$hcountry['name'],
            'hregion'=>$hregion['name'],
            'hcity'=>$hcity['name'],
        );
        $this->view->region = $array;
        $user_language = new Application_Model_UsersLanguage();
        $language = new Application_Model_Language();
        
        $language_str = $user_language->fetchUser($user['id']);
        $language_mas = explode(";",$language_str['language_id']);
        foreach ($language_mas as $k =>$v)
        {
            $lang = $language->fetchOne($v);
            $language_result[$k] = (array)[$lang->title]; 
        }
        $this->view->language_result = $language_result; 
        
        $user_spec = new Application_Model_UsersSpecialization();
        $specialization = new Application_Model_Specialization();
        
        $spec_str = $user_spec->fetchUser($user['id']);
        $spec_mas = explode(";",$spec_str['specialization_id']);
        foreach ($spec_mas as $k =>$v)
        {
            $spec = $specialization->fetchOne($v);
            $spec_result[$k] = (array)[$spec->title]; 
        }
        $this->view->spec_result = $spec_result; 
        $this->view->email = $user->email;
        $this->view->skype = $user->skype;
        
        $test = "";
        
        
        
    }
    
   
    function place_info($country="",$region="")
    {
        $regions = new Application_Model_Regions();
        $result_array = array();
        if ($country=="" AND $region=="") 
           $res_array = $regions->fetchCountry();
        if ($country AND $region =="") // искать регион 
           $res_array = $regions->fetchRegion($country);
        if ($country !="" AND $region !="") // искать город 
           $res_array = $regions->fetchCity($country,$region);
           
        
        if (!empty($res_array))
        {
            foreach ($res_array as $key => $val) 
            {
                $result_array[$val->id] = $val->name;
            }
        }
        
        return $result_array;
    }
    
    function toolsAction() 
    {   
        switch ($_GET['act']) 
        {
            case 'main':
                $this->mainToolAction();
                break;
            case 'contacts':
                $this->contactsToolAction();
                break;
            case 'career':
                $this->careerToolAction();
                break;
            default :
                $this->mainToolAction();
                break;
        }  
    }
    
    function mainToolAction() 
    {
        $model = new Application_Model_User();
        $userlanguage = new Application_Model_UsersLanguage();  
        $userspecialization = new Application_Model_UsersSpecialization();
        $language = new Application_Model_Language();
        $specialization = new Application_Model_Specialization();
        $user_id = Zend_Auth::getInstance()->getIdentity()->id; // Текущий юзер айди
        $user_settings =  $model->userinfo($user_id);
         
        $this->view->name = $user_settings->name; 
        $this->view->family = $user_settings->family; 
        $this->view->patronamic = $user_settings->patronamic; 
        $this->view->sex = $user_settings->sex; 
        
        
        if ($this->getRequest()->isXmlHttpRequest()) 
        {
            if($_POST['action'] == 'show_language') 
                die($this->language($this->getRequest()->getPost('value'),$this->getRequest()->getPost('def')));
            else if($_POST['action'] == 'show_specialization')  
                die($this->specialization($this->getRequest()->getPost('value'),$this->getRequest()->getPost('def')));
            else
            {
                $name = $this->getRequest()->getPost('name');
                $language = $this->getRequest()->getPost('language');
                $specialization = $this->getRequest()->getPost('specialization');
                $family = $this->getRequest()->getPost('family');
                $sex = $this->getRequest()->getPost('sex');
                $patronamic = $this->getRequest()->getPost('patronamic');
                $date_birth = $this->getRequest()->getPost('year').'-'.$this->getRequest()->getPost('mon').'-'.$this->getRequest()->getPost('day');
                $date_birth = strtotime($date_birth);
                if($name == '' || $family == '' || $sex == 0) 
                {
                    $message = 'Поля со звездочкой - обязательны для заполнения';
                    $status = 0;
                }
                else
                {
                   
                    $data = implode(";", $language);
                    $userlanguage->update($user_id,$data);
                    $data = implode(";", $specialization);
                    $userspecialization->update($user_id,$data);
                    $data = array(
                       'name' => $name,
                       'family' => $family,
                       'sex' => $sex,
                       'date_birth' => $date_birth,
                       'patronamic' => $patronamic
                    );
                    $model->update($user_id,$data);
                    $message = 'Данные успешно сохранены';
                    $status = 1;
                }
                  
                  die (Zend_Json::encode(array('status' => $status, 'message' => $message)));
            }
            
        }
        
        for($i=(date("Y")-100);$i<=date("Y");$i++)
        { 
            $year[$i] = $i; 
        } 
        $res = $model->backdate($user_id); // res стал обьектом
        $date_birth = $res->date_birth;
        $days_in_mounth = cal_days_in_month(CAL_GREGORIAN, date("m",$date_birth), date("Y",$date_birth));
        for ($i=1;$i<=$days_in_mounth;$i++)
        {
            $day[$i]=$i;
        }
        $this->view->day = $day;
        $this->view->date_day = date("d",$date_birth);
        $this->view->date_mon = date("m",$date_birth);
        $this->view->date_year = date("Y",$date_birth);
        $this->view->year = $year;
        $mon = array('01'=>'января','02'=>'февраля','03'=>'марта','04'=>'апреля','05'=>'мая','06'=>'июня','07'=>'июля','08'=>'августа','09'=>'сентября','10'=>'октября','11'=>'ноября','12'=>'декабря');
        $this->view->mon = $mon;
        
        $language_my = $language->fetchAll();            
        foreach($language_my as $key => $value)
        {
            $language_mas[$value->id] = $value->title;
        }
        $curlanguagestr = $userlanguage->fetchUser($user_id); // обьект языков текущего пользователя
        $curlanguagemas = explode(";",$curlanguagestr->language_id);  // массив языков текущего пользователя
        
        $this->view->result_lang = $curlanguagemas;
        $this->view->all_language = $language_mas;
        
        $spec_my = $specialization->fetchAll();
        foreach($spec_my as $key => $value)
        {
            $spec_mas[$value->id] = $value->title;
        }
        $curspecstr = $userspecialization->fetchUser($user_id); // обьект языков текущего пользователя
        $curspecmas = explode(";",$curspecstr->specialization_id);  // массив языков текущего пользователя
      
        $this->view->result_spec = $curspecmas;
        $this->view->spec_all = $spec_mas;
        
        $user = $model->$user_id;
        $this->view->user = $user; 
        $this->render('tools-main');
    }
    
    function getdateAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {    
            $month = $this->getRequest()->getPost('mounth'); 
            $year = $this->getRequest()->getPost('year'); 
            $days_in_mounth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            die (Zend_Json::encode(array('dayz' => $days_in_mounth,'status' => $month, 'message' => $year)));
        }
    }
    
    function contactsToolAction() 
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            
            if(isset($_POST['action']))
            {
                switch ($_POST['action']) 
                {
                    case 'show_region': 
                        $regions = $this->place_info($_POST['country']);                       
                        die (Zend_Json::encode(array('regions' => $regions )));
                        break;
                    case 'show_city' :
                        $citys = $this->place_info($_POST['country'],$_POST['region']);
                        die (Zend_Json::encode(array('citys' => $citys )));
                        break;
          
                }
            }
            else
            {
                $country = $this->getRequest()->getPost('country');
                $region = $this->getRequest()->getPost('region');
                $city = $this->getRequest()->getPost('city');
                $hcountry = $this->getRequest()->getPost('hcountry');
                $hregion = $this->getRequest()->getPost('hregion');
                $hcity = $this->getRequest()->getPost('hcity');
                $phone = $this->getRequest()->getPost('phone');
                $phone_dop = $this->getRequest()->getPost('phone_dop');
                $site = $this->getRequest()->getPost('site');
                $skype = $this->getRequest()->getPost('skype');
                $data = array(
                  'country' =>     $country,
                  'region' =>      $region,
                  'city' =>        $city,
                  'hcountry' =>    $hcountry,
                  'hregion' =>     $hregion,
                  'hcity' =>       $hcity,
                  'phone' =>       $phone,
                  'phone_dop' =>   $phone_dop,
                  'site' =>        $site,
                  'skype' =>       $skype
               );
               $user_id = Zend_Auth::getInstance()->getIdentity()->id;
               $users = new Application_Model_User();
               $users->update($user_id,$data);
               $message = 'Данные успешно сохранены.';
               $status = 1;
               die (Zend_Json::encode(array('status' => $status, 'message' => $message)));
            }
        }
        $users = new Application_Model_User();
        $user = $users->userinfo(Zend_Auth::getInstance()->getIdentity()->id);
        $this->view->user = $user;
        $this->view->country = $this->place_info();
        $this->view->region = $this->place_info($user->hcountry);
        $this->view->city = $this->place_info($user->hcountry,$user->hregion);
        $this->view->hregion = $user->hregion;
        $this->view->hcity = $user->hcity;
        
        $this->render('tools-contacts');
    }
    
    function careerToolAction () 
    {
        
         if ($this->getRequest()->isXmlHttpRequest())
         {
               $user_id = Zend_Auth::getInstance()->getIdentity()->id;
               $career_id = $this->getRequest()->getPost('career_id'); 
               $type_career = $this->getRequest()->getPost('type_career');
               
               foreach ($career_id as $key => $val)
               {
                   $country = $this->getRequest()->getPost('country-name');
                   $city = $this->getRequest()->getPost('city-name');
                   
                   if ($type_career[$key] == 1)  // insert data
                   {
                       $company_name = $this->getRequest()->getPost('company-name');
                       $company_adres = $this->getRequest()->getPost('place-name');
                       $country = $this->getRequest()->getPost('country-name');
                       $city = $this->getRequest()->getPost('city-name');
                       $job_name = $this->getRequest()->getPost('job-name');
                       $year_from = $this->getRequest()->getPost('year-from');
                       $year_to = $this->getRequest()->getPost('year-to');
                        
                       $data = array(
                          'user_id' =>   $user_id,
                          'country' =>   $country[$key],
                          'city' =>      $city[$key],
                          'company' =>   $company_name[$key],
                          'place' =>     $company_adres[$key],
                          'job' =>       $job_name[$key],
                          'year_from' => $year_from[$key],
                          'year_to' =>   $year_to[$key]
                       );
                       if ($country[$key] == 0 OR $city[$key] == 0)
                       {
                           $message = 'Не все поля заполнены.';
                       }
                       else
                       {   
                           if ($company_name[$key]!="" AND $company_adres[$key]!="" AND $job_name[$key]!="" AND $year_from[$key]!="" AND $year_to[$key]!="")
                           {    
                               $career = new Application_Model_Career();
                               $career->insert($data); 
                               $message = 'Данные успешно сохранены.';
                           }
                       }
                   }   
                   else // update data
                   {    
                      
                        $company_name = $this->getRequest()->getPost('company-name');
                        $company_adres = $this->getRequest()->getPost('place-name');
                        $country = $this->getRequest()->getPost('country-name');
                        $city = $this->getRequest()->getPost('city-name');
                        $job_name = $this->getRequest()->getPost('job-name');
                        $year_from = $this->getRequest()->getPost('year-from');
                        $year_to = $this->getRequest()->getPost('year-to');
                       
                      //  die($val."====");
                        
                        if ($country[$key]==0 OR $city[$key]== 0)
                        {
                          
                              $career = new Application_Model_Career();
                              $career->delete_career($career_id[$key]);
                        }
                        
                        $data = array(
                           'country' =>   $country[$key],
                           'city' =>      $city[$key],
                           'company' =>   $company_name[$key],
                           'place' =>     $company_adres[$key],
                           'job' =>       $job_name[$key],
                           'year_from' => $year_from[$key],
                           'year_to' =>   $year_to[$key]
                        );
                        $career = new Application_Model_Career();
                        $career->update($val,$data);
                        $message = 'Данные успешно сохранены.';
                   }
               }
               
               $status = 1;
               die (Zend_Json::encode(array('status' => $status, 'message' => $message)));
         }
       
        $career = new Application_Model_Career();
        $this->view->career = $career->fetchAll();
        $regions = new Application_Model_Regions();
        $result_mas = $career->fetchAll();
        $id_country = $result_mas[0]['country'];
        $id_city = $result_mas[0]['city'];
       
        foreach($result_mas as $key => $value)
        {
            $country_list[] = $value->country;
            $city_list[] = $regions->fetchCity($country_list[$key],0)->toArray();
        }
        
        $new_city_list = array();
        foreach ($city_list as $key => $value) 
        {
            $new_city_list[$key][0] = 'не выбрано';
            foreach($value as $k => $v)
            {
                 $new_city_list[$key][$v['id']] = $v['name'];
            }
            
        }
        
        $this->view->city_list = $new_city_list;
        $arr_count = $this->place_info();
        $arr_count = array(0 => 'не выбрано')+$arr_count;
        $this->view->country = $arr_count;
        $city_mas = $regions->fetchCity($id_country,0);
        foreach($city_mas as $key => $val)
        {
            $city_ar[$key]=$val->name;
        }    
        $this->view->city = $city_ar;
        $this->render('tools-career');
        
    }
    function sitylistAction()
    {
        $id_country = $this->getRequest()->getPost('country_cur');
        $regions = new Application_Model_Regions();
        $city_mas = $regions->fetchCity($id_country,0);
        foreach($city_mas as $key => $val)
        {
            $city_name[$val->id]=$val->name;
        } 
        asort($city_name);
        $message = 'Данные успешно сохранены.';
        $status = 1;
        die (Zend_Json::encode(array('data_name' => $city_name, 'status' => $status, 'message' => $message)));
        
    }
    function addcareerAction ()
    {
        if(isset($_POST['id']))
        {
            $user_id = Zend_Auth::getInstance()->getIdentity()->id;
            $career = new Application_Model_Career();
            
            $status = 1;
            $message = "ok";
            
            $country_list = $this->place_info();
            $country_list = array(0 => 'не выбрано')+$country_list;
            $this->view->country = $country_list;
            $this->view->last_id = $_POST['id']; 
            $as = $this->view->render('profile/add-career.phtml');
            die (Zend_Json::encode(array('html' =>$as, 'status' => $status, 'message' => $message)));
        }
        
    }
    function delcareerAction ()
    {
        if(isset($_POST['del']))
        {
            $status = 1;
            $message = 'Успешно удалено';
            $ok = 'test' ;   
            die (Zend_Json::encode(array('ok' =>$ok, 'status' => $status, 'message' => $message)));
        }
    }
    
    function addfriendrequestAction ()
    {
        if(isset($_POST['freind_id']) AND is_numeric($_POST['freind_id']))
        {
            $user_friend_request = new Application_Model_UsersFriendsRequest();
            $id = $_POST['freind_id'];
          //  $data = array(
         //     'friends_id' => 'CONCAT(friends_id,'".$id."')'  
         //   );
            
          //  $u_f_r = $user_friend_request->update($data);
            $status = 1;
            $message = 'Успешно удалено';
            
            $ok = $_POST['freind_id'] ;   
            die (Zend_Json::encode(array('ok' =>$ok, 'status' => $status, 'message' => $message)));
        }
    }
    
                   
        
  
    
    
}


