import React, { useState, useEffect ,useLayoutEffect } from 'react';
import Axios from 'axios';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';
import XMLViewer from 'react-xml-viewer';
import Spinner from '../Spinner';
import FileDownload from 'js-file-download';
import { CSVLink, CSVDownload } from "react-csv";
import { CsvToHtmlTable } from 'react-csv-to-table';

///api/authors/export/CSV/with-books
export default function (props) {
    const [type,setType] = useState('');
    const [content,setContent] = useState('');
     const [status,setStatus] = useState('');
    const [nesting,setNesting] = useState('');
    const [data, setData] = useState('');
    const [url,setURL] = useState('');
    //for fetching data after forms are filled:
    useLayoutEffect( () =>{
            if (status=== "loading"){
                Axios.get(url).then(
                    (res)=>{
                        console.log(res.data,"res");
                        setData(res.data);
                        setStatus("done");


                    }
                );
            }

        }
        , [status] )
    if (status === "loading"){
        return <Spinner/>;
    }
    if (status ==="done" && type === "xml"){

        return (
            <div>
                <h2> Displaying {content} XML </h2>
                <XMLViewer xml={data}/>
            </div>
        );
    }
    if (status ==="done" && type === "csv"){
        return (
            <div>
            <h2> Displaying {content} CSV </h2>
            <p>As a plain text :<br/>
        <CsvToHtmlTable
        data={data}
        csvDelimiter="," /> </p>
            <p> As a table :
            <br/>
        <CsvToHtmlTable
        data={data}
        csvDelimiter=","
        tableClassName="table table-striped table-hover"
            /> </p>
            <CSVLink data={data} >
            Download me
        </CSVLink>
            </div>
        ) ;
    }
    if (status==="download" && type === "xml"){
        return FileDownload(data,"data.xml");
    }



    return (
        <Form onSubmit =
        { (e) =>{
            e.preventDefault();
            var url = "/api/" + content + "/export/" + type.toUpperCase()
            console.log("URL", url);
            setURL(url);

            if (content === "authorsAndBooks"){
                if (type === "xml"){

                }
                else{

                }
            }
            setStatus("loading");


        }}>


    <fieldset>
        <Form.Group as={Row}>
            <Form.Label as="type" column sm={2}>
                Export to:
            </Form.Label>
            <Col sm={10}>
                <Form.Check
                type="radio"
                label="XML"
                name="type"
                id="xml"
                onClick={e=>setType(e.target.id)}
                required
                />
                <Form.Check
                type="radio"
                label="CSV"
                name="type"
                id="csv"
                onClick={e=>setType(e.target.id)}
                />
            </Col>
        </Form.Group>
     </fieldset>

     <fieldset>
    <Form.Group as={Row}>
    <Form.Label as="content" column sm={2}>
        Radios
        </Form.Label>
        <Col sm={10}>
        <Form.Check
        type="radio"
        label="books only"
        name="content"
        id="books"
        onClick={e=>setContent(e.target.id)}
        />
        <Form.Check
    type="radio"
    label="authors only"
    name="content"
    id="authors"
        />
        <Form.Check
    type="radio"
    label="authors and books"
    name="content"
    id="authorsAndBooks"
    required
        />
        </Col>
        </Form.Group>
        </fieldset>
        <Button variant="primary" type="submit"> submit </Button>
        </Form>
    );

}
