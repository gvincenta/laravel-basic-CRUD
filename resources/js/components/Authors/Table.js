import React, { useState, useEffect } from 'react';
import Axios from 'axios';
import {Table} from 'react-bootstrap';
import Item from './Item';
import Spinner from '../Spinner';
export default function Main(props) {
    const [authors,setAuthors] = useState([]);
    const [status,setStatus] = useState('');

    useEffect(() => {
        Axios.get('/api/authors')
            .then((res)=>{
                console.log(res, "Authors Table");
                setAuthors(res.data);
                setStatus("done");
            })

    },[status])
    //display all authors after fetching:
    if (authors.length > 0){
        return (
            <Table striped bordered hover>
                <thead>
                    <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    </tr>
                </thead>
                <tbody>
                    {
                        authors.map(v => {
                            return <Item key={v.ID} author={v}/>
                        })
                    }
                </tbody>
            </Table>);
    }

    return (<Spinner/>);
}
