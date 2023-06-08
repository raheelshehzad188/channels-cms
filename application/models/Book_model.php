<?php
class Book_model extends CI_Model {
        private $table= 'books';

        public function searchAuthor($txt)
        {
                $this->db->like('name', $txt);
                $this->db->where('status', 0);
                return $this->db->get('author')->result_array();
        }
        public function bulk_insert($arrayData)
        {
            return $this->db->insert_batch($this->table, $arrayData); 
        }
        public function gen_new($mediaID)
    {
         $row= $this->db->where('mediaID', $mediaID)->get('media')->row();
        if($row && empty($row->resizer_path))
        {
                $content = file_get_contents('https://api.imageresizer.io/v1/images?key=79c401225a58a69ea28d96f5bdd512e98016ccb9&url='.$row->url);
            $ar = json_decode($content, true);
            $thumb =  $url;
            if($ar['success'])
            {
                $resizer = 'https://im.ages.io/'.$ar['response']['id'];
                $rest  = \Cloudinary\Uploader::upload($resizer, array());
                $nrl = $rest['url'];
                $up = array(
                    "public_id"=> $rest['public_id'],
                    "url"=> $rest['url'],
                    "resizer_path"=>$resizer
                    );
                    if($this->db->where('mediaID', $mediaID)->update('media',$up))
                    {
                        return true;
                    }
            }
                
        }
            
    }
        public function getMediaByID($mediaID)
        {
            $this->gen_new($mediaID);
         $row= $this->db->where('mediaID', $mediaID)->get('media')->row();
        if($row)
        {

                return $row;
        }   
        }
        public function getAuthorByID($authorID)
        {
         $row= $this->db->where('authorID', $authorID)->get('author')->row();
        if($row)
        {

                return $row;
        }
        }   
        public function getGenreByBookID($bookID)
        {
         $row= $this->db->select('book_to_genre.genre_id,genres.name')->where('book_id', $bookID)->join('genres','genres.genreID=book_to_genre.genre_id ')->get('book_to_genre')->result_array();
        if($row)
        {

                return $row;
        }   
        }
        public function getcheckBookIsBorrow($bookID)
        {
           return $this->db->where('bookID',$bookID)->where('status','accept')->get('book_barrow')->row();   
        }
        public function getcheckBookIsRequested($bookID,$userid=0)
        {
           if($userid==0)
           return false; 
           return $this->db->where('bookID',$bookID)->where('to',$userid)->where('status','onrequest')->get('book_barrow')->row();   
        }
        
        public function delGenreByBookID($bookID)
        {
            return $this->db->where('book_id',$bookID)->delete('book_to_genre');
        }
        public function delLangByBookID($bookID)
        {
            return $this->db->where('book_id',$bookID)->delete('book_to_lang');
        }
        public function delTagByBookID($bookID)
        {
            return $this->db->where('book_id',$bookID)->delete('book_to_tag');
        }
        public function getTagsByBookID($bookID)
        {
         $row= $this->db->select('tags.tagID,tags.name as name')->where('book_id', $bookID)->join('tags','book_to_tag.tag_id = tags.tagID')->get('book_to_tag')->result_array();
        if($row)
        {

                return $row;
        }   
        }   
        public function getLangByBookID($bookID)
        {
         $row= $this->db->select('list.id,list.value as name')->where('book_id', $bookID)->join('list','book_to_lang.list_id = list.id')->get('book_to_lang')->result_array();
        if($row)
        {

                return $row;
        }   
        }
        public function getAuthorID($data)
        {
                $row= $this->db->where('name', $data['name'])->get('author')->row();
                if($row)
                {

                        return $row->authorID;
                }
                else
                {

                        if($this->db->insert('author', $data))
                        {
                                // die("add".$this->db->insert_id());
                         return $this->db->insert_id();       
                        }
                        else
                        {
                            DIE("hERE");
                                return 0;
                        }
                        
                }
        }
        public function getGenricID($data)
        {
                $row= $this->db->where('name', $data['name'])->where('userID',$data['userID'])->get('genres')->row();
                if($row)
                {

                        return $row->genreID;
                }
                else
                {

                        if($this->db->insert('genres', $data))
                        {
                                // die("add".$this->db->insert_id());
                         return $this->db->insert_id();       
                        }
                        else
                        {
                            DIE("hERE");
                                return 0;
                        }
                        
                }
        }
        public function getTagID($data)
        {
                $row= $this->db->where('name', $data['name'])->get('tags')->row();
                if($row)
                {

                        return $row->authorID;
                }
                else
                {

                        if($this->db->insert('tags', $data))
                        {
                                // die("add".$this->db->insert_id());
                         return $this->db->insert_id();       
                        }
                        else
                        {
                                $error = $this->db->last_query();
                                print_r($error);
                                die();
                        }
                        
                }
        }
        public function getHomeProducts($limit,$startindex)
        {
            
            if($startindex > 1){
            $startindex = ($startindex-1) * $limit;
            }
                
                $this->db->select('books.*');
                $this->db->from('books');
                $this->db->limit($limit, $startindex);
                $this->db->order_by('bookID', 'desc');
                $this->db->where('status', 0);


            return $this->db->get()->result_array();
        }
        public function get_available_book_total($limit_per_page= 0,$startindex=0, $key=null)
        {
             if($startindex > 1){
            $startindex = ($startindex-1) * $limit_per_page;
            }
            $notAvailabllebooks=$this->db->where('status','accept')->get('book_barrow')->result_array();
            $bookes=[];
            if(count($notAvailabllebooks)>0){
                foreach ($notAvailabllebooks as $value) {
                    $bookes[]=$value['bookID'];
                }
            }
            $author=$this->db->like('name',$key)->where('status',0)->get('author')->result_array();
            $authors=[];
            if(!empty($author))
            {
                foreach ($author as  $value) {
                    $authors[]=$value['authorID'];
                }
            }
            $this->db->select('books.*');
            $this->db->from('books');
            $this->db->where('books.status',0);
            if(!empty($bookes) && count($bookes)>0)
            {    
            $this->db->where_not_in('books.bookID',$bookes);
            }
            if(!empty($authors) && count($authors)>0)
            {    
            $this->db->where_in('books.authorID',$authors);
            }
            else
            {
                   
                    $this->db->like('books.title',$key);
                    $this->db->or_where('books.isbnNO',$key);
                
            }
            if($limit_per_page!=0)
            $this->db->limit($limit_per_page,$start_index);
            $this->db->order_by('books.bookID', 'desc');
            return $this->db->get()->result_array();    
        }
        public function get_total()
        {
             return count($this->db->where('status', 0)->get('books')->result_array());
        }
        public function get($data)
        {
                if(!$data)
                {
                        return $this->db->get('books')->result_array();
                }
                else
                {
                        return $this->db->where($data)->get('books')->result_array();
                }
        }
        public function getGenere($data = array())
        {
                if(!$data)
                {
                        return $this->db->get('genres')->result_array();
                }
                else
                {
                        return $this->db->where($data)->get('genres')->result_array();
                }
        }

        public function addMedia($ar)
        {
            
            $this->db->insert('media', $ar);
            return $this->db->insert_id();

        }
        public function addBookGenre($book_id, $genre_id)
        {
            $ar = array(
                "id"=> '',
                "book_id"=> $book_id,
                "genre_id"=> $genre_id
            );
            $this->db->insert('book_to_genre', $ar);

            return $this->db->insert_id();

        }
        public function addBookLang($book_id, $list_id)
        {
            $ar = array(
                "id"=> '',
                "book_id"=> $book_id,
                "list_id"=> $list_id
            );
            $this->db->insert('book_to_lang', $ar);
            return $this->db->insert_id();

        }
        public function addBookTag($book_id, $tag_id)
        {
            $ar = array(
                "id"=> '',
                "book_id"=> $book_id,
                "tag_id"=> $tag_id
            );
            $this->db->insert('book_to_tag', $ar);
            return $this->db->insert_id();

        }
        public function getTag($data = array())
        {
                if(!$data)
                {
                        return $this->db->get('tags')->result_array();
                }
                else
                {
                        return $this->db->where($data)->get('tags')->result_array();
                }
        }
        public function getList($data = array())
        {
            if(!$data)
            {
                    return $this->db->get('list')->result_array();
            }
            else
            {
                    return $this->db->where($data)->get('list')->result_array();
            }
        }
        public function update($bookID, $data)
        {
                return $this->db->where('bookID', $bookID)->update($this->table, $data);
        }
        public function delete($tagID)
        {
                return $this->db->where('tagID', $tagID)->delete();
        }
        public function add($data)
        {
                if($this->db->insert($this->table,$data))
                {
                    return $this->db->insert_id();
                }

        }
         public function getBookByGroupCount($groupid)
        {
            return count($this->db->where('status',1)->where('groupID',$groupid)->get('books')->result_array());
        }
        public function login($uname, $upass)
        {
                $upass = md5($upass);
                $sql = "select * from `users` WHERE email = '$uname'  OR `uname` = '$uname' AND `upass` = '$upass'";
 
                $query = $this->db->query($sql);
                return $query->row();
        }
        public function updateuserbyid($userID, $data)
        {
                return $this->db->where('UserID',$userID)->update('users',$data);

        }
        public function getrolebyid($roleID)
        {
                $this->db->where('roleID',$roleID);
 
                $query = $this->db->get('roles');
                return $query->row();
        }

        public function insert_entry()
        {
                $this->title    = $_POST['title']; // please read the below note
                $this->content  = $_POST['content'];
                $this->date     = time();

                $this->db->insert('entries', $this);
        }
        public function fine_search_book($key,$limit_per_page=0, $start_index=0)
        {
            if($startindex > 1){
            $startindex = ($startindex-1) * $limit_per_page;
            }
            $author=$this->db->like('name',$key)->where('status',0)->get('author')->result_array();
            $authors=[];
            if(!empty($author))
            {
                foreach ($author as $value) {
                    $authors[]=$value['authorID'];
                }
            }
            $this->db->select('books.*');
            $this->db->from('books');
            $this->db->join('author','author.authorID = books.authorID');
            $this->db->join('media','media.mediaID = books.coverImg');
            $this->db->where('books.status',0);
            if(!empty($authors) && count($authors)>0)
            {    
            $this->db->where_in('books.authorID',$authors);
            }
            else
            {
            $this->db->like('books.title',$key);
            $this->db->or_where('books.isbnNO',$key);
            }
            if($limit_per_page!=0)
            $this->db->limit($limit_per_page,$start_index);
            $this->db->order_by('bookID', 'desc');
            return $this->db->get()->result_array();    
        }
        public function bookinfo($bookid)
        {
            $this->db->select('books.*,author.name AS name,media.url AS url,users.first_name AS first_name,users.last_name AS last_name,users.email AS email');
            $this->db->from('books');
            $this->db->where('books.bookID',$bookid);
            $this->db->where('books.status',0);
            $this->db->join('author','author.authorID = books.authorID');
            $this->db->join('media','media.mediaID = books.coverImg');
            $this->db->join('users','users.UserID = books.uid');
            return $this->db->get()->row();

        }
        public function bookingForBarrow($data)
        {
           $this->db->insert('book_barrow', $data);
           return $this->db->insert_id();
        }
        public function getBorrow($userid)
        {
            $this->db->select('books.*,book_barrow.barrowID , book_barrow.created_at AS date,book_barrow.from,users.first_name AS first_name,users.last_name AS last_name,users.email AS email');
            $this->db->from('book_barrow');
            $this->db->join('books','books.bookID=book_barrow.bookID');
            $this->db->join('users','users.UserID=book_barrow.from');
            $this->db->where('book_barrow.to',$userid);
            $this->db->where('books.status',0); 
            $this->db->where('book_barrow.status','accept');
           return $this->db->get()->result_array();
        }
        public function getBorrowDetail($barrowID)
        {
            $this->db->select('books.*,book_barrow.barrowID , book_barrow.created_at AS date,book_barrow.from,users.first_name AS first_name,users.last_name AS last_name,users.email AS email,author.name AS name');
            $this->db->from('book_barrow');
            $this->db->join('books','books.bookID=book_barrow.bookID');
            $this->db->join('author','author.authorID=books.authorID');
            $this->db->join('users','users.UserID=book_barrow.from');
            $this->db->where('book_barrow.barrowID',$barrowID);
            $this->db->where('books.status',0); 
           
            return $this->db->get()->row();
        }
        public function getLent($userid)
        {
            $this->db->select('books.*,book_barrow.barrowID , book_barrow.created_at AS date,book_barrow.to ,users.first_name AS first_name,users.last_name AS last_name,users.email AS email');
            $this->db->from('book_barrow');
            $this->db->join('books','books.bookID=book_barrow.bookID');
            $this->db->join('users','users.UserID=book_barrow.to');
            $this->db->where('book_barrow.from',$userid);
            $this->db->where('books.status',0); 
            $this->db->where('book_barrow.status','accept'); 
           return $this->db->get()->result_array();
        }
        public function bookingBarrowStatus($id,$data)
        {
           return $this->db->where('barrowID',$id)->update('book_barrow', $data);
        }
        public function notifiStore($data)
        {
           $this->db->insert('notifcation', $data);
           return $this->db->insert_id();
        }
        public function mailByUserId($userid,$all='')
        {
            $result=$this->db->where('UserID',$userid)->get('users')->row();
            if($all=='')
            return $result->email;
            return $result;
        }
        public function getBorrowBookInfo($barrowID)
        {
            return $this->db->where('barrowID',$barrowID)->get('book_barrow')->row();
        }
        public function getPreviousChat($barrowID)
        {
            $this->db->select('chat.*,message.messageID AS messageID,message.message AS message,message.sender_id AS sender_id,message.status AS mstatus ,message.created_at AS mcreated_at,users.first_name AS first_name,users.last_name AS last_name,users.email AS email');
            $this->db->from('chat');
            $this->db->join('message', 'message.chat_id = chat.chatID', 'left');
            $this->db->join('users', 'users.UserID = message.sender_id', 'left');
            $this->db->where('chat.barrowID',$barrowID);
            return $this->db->get()->result_array();
        }
        public function chatStore($chatdata)
        {
            $this->db->insert('chat', $chatdata);
            return $this->db->insert_id();
        }
        public function addMessage($messagedata)
        {
            $this->db->insert('message', $messagedata);
            $id=$this->db->insert_id();
            return $this->db->where('messageID',$id)->get('message')->row();
        }
        

}