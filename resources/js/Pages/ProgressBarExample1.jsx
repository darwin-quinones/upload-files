
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
        const source = new EventSource(URL_PROCESS);

        source.addEventListener('message', (event) => {
            const data = JSON.parse(event.data);
            setProgress(data.progress);
        });

        source.addEventListener('error', (event) => {
            console.log(event);
            source.close();
        });

        return () => {
            source.close();
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


