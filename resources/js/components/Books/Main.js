import React, { useState, useEffect } from 'react';
import {Nav} from 'react-bootstrap';
import Axios from 'axios';
import ReactTable from 'react-table-6'
import 'react-table-6/react-table.css'
import Spinner from '../Spinner';
import {Button} from 'react-bootstrap';
export default function Main(props) {

    const [data, setData] = useState(props.data||[]);
    const [status, setStatus] = useState(props.status||'loading');
    const columns = [
        {Header: 'bookID',
         accessor: 'books_ID',
         Cell: props => {
                     if (props.value){
                             return <div>  { props.value} <Button onClick={
                                    (e)=>{
                                    e.preventDefault();
                                    //TODO: PROMPT USERS BEFORE DELETE:
                                    console.log(props.value, "ONCLICK");
                                    Axios.delete("/api/books", {
                                        data :{
                                            'ID' : props.value
                                        }
                                    }).then((res) =>{
                                        console.log(res,"AFTER DELETE");
                                        //handle success / failure:
                                        window.location.reload();


                                    })
                                    }
                             }> delete </Button>   </div>
                     }else{
                     return <td></td>
                     }
                  } } ,
        {Header: 'Title',
        accessor: 'title'},
        {Header: 'authorID',
          accessor: 'ID'},
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
                <ReactTable
                    data={data}
                    columns={columns}
                    defaultPageSize={5}
                   />
           );
    }

    //display loading animation if data hasn't been fetched yet:
    return <Spinner/>

}
