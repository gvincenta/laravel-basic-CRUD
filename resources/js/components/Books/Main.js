import React, { useState, useEffect } from 'react';
import {Nav} from 'react-bootstrap';
import Axios from 'axios';
import ReactTable from 'react-table-6'
import 'react-table-6/react-table.css'
import Spinner from '../Spinner';

 export default function Main(props) {

    const [books, setBooks] = useState([]);
    const [status, setStatus] = useState('');
    const columns = [
        {Header: 'bookID',
         accessor: 'books_ID'} ,
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
        Axios.get('/api/books')
              .then((res) => {
                    console.log("Main",res);
                    setBooks(res.data);
                    setStatus("done");
                });
     },[status]);

    //display books and authors data:
    if (books.length >0){
           return (
                <ReactTable
                    data={books}
                    columns={columns}
                    defaultPageSize={5}
                   />
           );
    }

    //display loading animation if data hasn't been fetched yet:
    return <Spinner/>

}
