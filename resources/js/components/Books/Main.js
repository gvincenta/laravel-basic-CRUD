import React, { useState, useEffect } from 'react';
import {Nav} from 'react-bootstrap';
import Axios from 'axios';
import ReactTable from 'react-table-6'
import 'react-table-6/react-table.css'
import Spinner from '../Spinner';
import {Button,Form,Row,Col} from 'react-bootstrap';
export default function  (props) {

    const [data, setData] = useState(props.data||[]);
    const [status, setStatus] = useState(props.status||'loading');
    const [oldFirstName,setOldFirstName] = useState('');
    const [oldLastName,setOldLastName] = useState('');
    const [newFirstName,setNewFirstName] = useState('');
    const [newLastName,setNewLastName] = useState('');
    const [ID,setID] = useState(null);

    const displayDeleteButton = (props) =>
    { if (props.value){

        return(
               <div>
                    { props.value} &nbsp;
                    <Button variant="danger"
                    onClick={ (e)=>{
                        e.preventDefault();
                        //TODO: PROMPT USERS BEFORE DELETE:
                        console.log(props.value, "ONCLICK");
                        Axios.delete("/api/books", {
                            data :{'ID' : props.value }})
                            .then((res) =>{
                                console.log(res,"AFTER DELETE");
                                //handle success / failure:
                                window.location.reload();
                            })
                        }
                    }>
                        delete
                    </Button>
               </div>
               )}else{return <div> </div>}
    }
    const displayUpdateButton = (props) =>
                                    {
                                    if (props.value){
                                        return(
                                               <div>
                                                    { props.value} &nbsp;
                                                    <Button
                                                    onClick={ (e)=>{
                                                        e.preventDefault();
                                                        setID(props.value);
                                                        setOldFirstName(props.original.firstName);
                                                        setOldLastName(props.original.lastName);
                                                        setStatus('changing');
                                                         

                                                    }}  >
                                                        change
                                                    </Button>
                                               </div>
                                               )}else{return <div> </div>}
                                    }
    const columns = [
        {Header: 'bookID',
         accessor: 'books_ID',
         Cell: props => displayDeleteButton(props)  },
        {Header: 'Title',
        accessor: 'title'},
        {Header: 'authorID',
          accessor: 'ID',
          Cell : props => displayUpdateButton(props)},
        {Header: 'firstName',
         accessor: 'firstName'},
        {Header: 'lastName',
         accessor: 'lastName'},
          ]
    //fetch books and authors from backend:
    useEffect(()=>{
        if (status==="loading"){

            Axios.get('/api/books')
                          .then((res) => {
                               // console.log("Main",res);
                               setData(res.data);
                                setStatus("done");
                            });
        }

     },[status]);

    //display books and authors data:
    if (data.length >0){
           return (
                <div>
                    <ReactTable
                        data={data}
                        columns={columns}
                        defaultPageSize={5}
                       />
                    {status==="changing"
                        ? <Form onSubmit={
                                                                  (e)=>{
                                                                      //avoid reloading:
                                                                      e.preventDefault();
                                                                      //update author's name:
                                                                      Axios.put('/api/authors',{ID, firstName:newFirstName, lastName:newLastName})
                                                                          .then((res)=>
                                                                              {console.log("res", res);
                                                                                  //TODO: HANDLE THIS LOGIC IN BACKEND
                                                                                  if (res.data.affectedRows == 1 ){
                                                                                      //sucessfully changed an author's name, re-fetch data again:
                                                                                      setMessage("succeed!");
                                                                                      setStatus("loading..");
                                                                                      window.location.reload();
                                                                                  } else{
                                                                                      setMessage("failed");
                                                                                  }
                                                                              }
                                                                          )
                                                                  }
                                                                  }>
                                  <Form.Text className="text-muted">
                                      Changing Author with  ID : {ID} and name : {oldFirstName + ' '+ oldLastName }
                                  </Form.Text>
                                  <Row>
                                  <Col sm="5">
                                  <Form.Control type="tex" placeholder="First Name" onChange={v => setNewFirstName(v.target.value)}  required />
                                  </Col>
                                  <Col sm="5">
                                  <Form.Control type="text" placeholder="Last Name" onChange={v => setNewLastName(v.target.value)}  required />
                                  </Col>
                                  <Col >
                                  <Button variant="primary" type="submit">

                                      Submit
                                      </Button>
                                   </Col>
                                  </Row>


                          </Form>

                        : null}
                </div>
           );
    }

    //display loading animation if data hasn't been fetched yet:
    return <Spinner/>

}
