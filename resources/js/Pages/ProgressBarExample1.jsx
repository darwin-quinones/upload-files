
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../bootstrap';
//base end point url
const FILE_UPLOAD_BASE_ENDPOINT = "http://127.0.0.1:8020/file-upload";
const URL_UPLOAD = 'http://localhost:8000/upload'
const URL_PROCESS = 'http://localhost:8000/process-file-uploaded'
export default function ProgressBarExample1() {
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        var evtSource = new EventSource(URL_PROCESS);

        evtSource.addEventListener("message", function (e) {

            var obj = JSON.parse(e.data);
            console.log(obj.progress);
            setProgress(obj.progress);
            // newElement.innerHTML = "ping at " + obj.time;
            // eventList.appendChild(newElement);
        }, false);



        // let source = new EventSource(URL_PROCESS);
        // console.log('source: 1', source)
        // source.onmessage = function(event){
        //     console.log('event.data: ', event.data)
        // }
        // source.addEventListener('message', (event) => {
        //     const data = JSON.parse(event.data);
        //     console.log('data complete: ' , data)
        //     setProgress(data.progress);
        // });

        evtSource.addEventListener('error', (event) => {
            console.log(event);
            evtSource.close();
        });

        return () => {
            evtSource.close();
        };
    }, []);

    const handleUpload = async (e) => {
        e.preventDefault();

        const file = e.target.file.files[0];
        const data = new FormData();
        data.append('file', file);

        // Upload the file to the server
        try {
            const response = await fetch('/upload', {
                method: 'POST',
                body: data
            });
            console.log(response);
        } catch (error) {
            console.log(error);
        }


    };

    return (
        <div>
            <form onSubmit={handleUpload}>
                <input type="file" name="file" />
                <button type="submit">Upload</button>
            </form>
            <div>{progress}%</div>
        </div>
    );
};


