import React, { useState } from 'react';
 import {Link} from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Spinner from '../Spinner';
import Main from '../Books/Main';
import Form from './Form';
/** for searching a book by its title / author: */

export default function (props){
    const [title,setTitle] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
     const [data,setData] = useState(null);
     const [searchBy, setSearchBy] = useState('');
    const [status,setStatus] = useState('');
    const getData = (e)=>{
        if (e){
            e.preventDefault();
        }
        setStatus("loading");
        Axios
            .get("/api/authors/with-filter",
                {
                    params:{
                        firstName,lastName,title
                    }
                })
            .then((res)=>{
                console.log(res.data);
                setData(res.data);

                setStatus("done");
            })
    }
    const columns = [
        {Header: 'authorID',
            accessor: 'ID'},
        {Header: 'firstName',
            accessor: 'firstName'},
        {Header: 'bookID',
         accessor: 'books_ID'},
        {Header: 'Title',
            accessor: 'title'}]
    if (status === "loading"){
        return <Spinner/>;
    }
    if (data  ){
        return (
            <Main data={data} status="done" onReload={getDataByTitle}/>);
    }



    return( <Form getData={getData} setSearchBy={setSearchBy} setTitle={setTitle} setFirstName={setFirstName} setLastName={setLastName}/>
        );
}
